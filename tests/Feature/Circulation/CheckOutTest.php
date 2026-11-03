<?php

namespace Tests\Feature\Circulation;

use Tests\TestCase;
use App\Models\Account;
use App\Models\Student;
use App\Models\Book;
use App\Models\BookDetail;
use App\Models\BookData;
use App\Models\BorrowingTransaction;
use App\Models\LibraryStatus;
use App\Models\BookCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Pages\Dashboard\CheckOutBook;
use Carbon\Carbon;

class CheckOutTest extends TestCase
{
    use RefreshDatabase;

    protected $librarian;
    protected $student;
    protected $book;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed core lookups
        LibraryStatus::create(['id' => 1, 'status' => 'Active']);
        LibraryStatus::create(['id' => 2, 'status' => 'Inactive']);
        BookCondition::create(['id' => 2, 'status' => 'Good']);

        // Create Librarian
        $this->librarian = Account::factory()->create(['role_id' => 3]);
        $this->librarian->librarian()->create([
            'first_name' => 'Librarian',
            'last_name' => 'User',
            'school_id_number' => 'EMP-TEST-001',
        ]);

        // Create Student
        $this->student = Student::create([
            'account_id' => Account::factory()->create(['role_id' => 4])->id,
            'school_id_number' => '2026-0001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'library_status_id' => 1, // Active
            'program' => 'BSCS',
            'year_level' => '4'
        ]);

        // Create physical book (available for checkout)
        $bookData = BookData::create(['book_title' => 'Test Book', 'subtitle' => 'BK', 'language_id' => \App\Models\Language::firstOrCreate(['lang' => 'English'])->id]);
        $bookDetail = BookDetail::create(['book_data_id' => $bookData->id, 'call_number' => 'CN-123', 'format' => 'Book', 'book_type_id' => \App\Models\BookType::first()->id, 'publisher_id' => \App\Models\Publisher::first()->id]);
        $this->book = Book::create([
            'book_detail_id' => $bookDetail->id,
            'book_condition_id' => 2, // Good
            'accession_number' => 'ACC-999',
            'code' => 'BAR-999',
            'status' => 'available',
            'location' => 'Shelf A'
        ]);
    }

    public function test_librarian_can_access_checkout_page(): void
    {
        $response = $this->actingAs($this->librarian)->get('/librarian/circulation-desk/borrow');
        $response->assertStatus(200);
    }

    public function test_selected_due_date_is_used_for_take_home_checkout(): void
    {
        $this->travelTo(Carbon::parse('2026-10-30 09:00', 'Asia/Manila'));
        config(['settings.library_config.loan_period' => 5]);

        Livewire::actingAs($this->librarian)->test(CheckOutBook::class)
            ->assertSet('dueDate', '2026-10-31')
            ->call('handleSearchCode', '2026-0001')
            ->call('handleSearchCode', 'BAR-999')
            ->call('confirmCheckout', '2026-11-02')
            ->assertDispatched('checkout-completed');

        $loan = BorrowingTransaction::where('book_id', $this->book->id)->firstOrFail();
        $this->assertSame('2026-11-02', $loan->due_date->copy()->setTimezone('Asia/Manila')->toDateString());
    }

    public function test_checkout_rejects_due_dates_outside_configured_limit(): void
    {
        $this->travelTo(Carbon::parse('2026-10-30 09:00', 'Asia/Manila'));
        config(['settings.library_config.loan_period' => 5]);

        foreach (['2026-10-29', '2026-11-05', 'invalid'] as $date) {
            Livewire::actingAs($this->librarian)->test(CheckOutBook::class)
                ->call('confirmCheckout', $date)
                ->assertDispatched('checkout-failed');
        }
        $this->assertDatabaseMissing('borrowing_transactions', ['book_id' => $this->book->id]);
    }

    public function test_periodical_keeps_its_shorter_loan_limit(): void
    {
        $this->travelTo(Carbon::parse('2026-10-30 09:00', 'Asia/Manila'));
        config(['settings.library_config.loan_period' => 5]);
        $this->book->bookDetail->periodicalIssue()->create(['borrowable' => true, 'loan_days' => 2]);

        Livewire::actingAs($this->librarian)->test(CheckOutBook::class)
            ->call('handleSearchCode', '2026-0001')
            ->call('handleSearchCode', 'BAR-999')
            ->call('confirmCheckout', '2026-11-04')
            ->assertDispatched('checkout-completed');

        $loan = BorrowingTransaction::where('book_id', $this->book->id)->firstOrFail();
        $this->assertSame('2026-11-01', $loan->due_date->copy()->setTimezone('Asia/Manila')->toDateString());
    }

    public function test_approved_reservation_link_loads_student_and_copy_without_checkout(): void
    {
        $this->book->update(['status' => 'reserved']);
        $reservation = \App\Models\BookReservation::create([
            'book_id' => $this->book->id, 'student_id' => $this->student->id,
            'account_id' => $this->student->account_id, 'reservation_status_id' => 2,
            'reservation_date' => now(), 'due_date' => now()->addDay(),
        ]);
        Livewire::actingAs($this->librarian)->withQueryParams(['reservation' => $reservation->id])
            ->test(CheckOutBook::class)
            ->assertSet('scannedMember.id', $this->student->id)
            ->assertSet('checkoutBooks.0.reservation_id', $reservation->id);
        $this->assertSame(2, (int) $reservation->fresh()->reservation_status_id);
        $this->assertSame('reserved', $this->book->fresh()->status);
    }

    public function test_reservation_search_lists_only_approved_pickups(): void
    {
        $this->book->update(['status' => 'reserved']);
        $reservation = \App\Models\BookReservation::create([
            'book_id' => $this->book->id, 'student_id' => $this->student->id,
            'account_id' => $this->student->account_id, 'reservation_status_id' => 1,
            'reservation_date' => now(), 'due_date' => now()->addDay(),
        ]);

        $modal = Livewire::actingAs($this->librarian)
            ->test(\App\Livewire\Components\Circulation\SearchEntityModal::class, ['service' => 'check-out'])
            ->call('openModal')
            ->assertSet('reservationCount', 0);

        $reservation->update(['reservation_status_id' => 2]);
        $modal->call('openModal')->assertSet('reservationCount', 1)
            ->assertSee('#RES-'.str_pad((string) $reservation->id, 5, '0', STR_PAD_LEFT));

        $reservation->update(['due_date' => now()->subMinute()]);
        $modal->call('openModal')->assertSet('reservationCount', 0);
    }

    public function test_fulfill_action_opens_checkout_without_changing_reservation(): void
    {
        $this->book->update(['status' => 'reserved']);
        $reservation = \App\Models\BookReservation::create([
            'book_id' => $this->book->id, 'student_id' => $this->student->id,
            'account_id' => $this->student->account_id, 'reservation_status_id' => 2,
            'reservation_date' => now(), 'due_date' => now()->addDay(),
        ]);
        Livewire::actingAs($this->librarian)->test(\App\Livewire\Pages\Reservation\AdminDashboard::class)
            ->call('fulfillReservation', $reservation->id)
            ->assertRedirect(route('librarian.circulation-desk.borrow', ['reservation' => $reservation->id]));
        $this->assertSame(2, (int) $reservation->fresh()->reservation_status_id);
        $this->assertSame('reserved', $this->book->fresh()->status);
    }

    public function test_unapproved_reservation_link_does_not_load_checkout(): void
    {
        $reservation = \App\Models\BookReservation::create([
            'book_id' => $this->book->id, 'student_id' => $this->student->id,
            'account_id' => $this->student->account_id, 'reservation_status_id' => 1,
            'reservation_date' => now(), 'due_date' => now()->addDay(),
        ]);
        Livewire::actingAs($this->librarian)->withQueryParams(['reservation' => $reservation->id])
            ->test(CheckOutBook::class)
            ->assertSet('scannedMember', null)
            ->assertCount('checkoutBooks', 0)
            ->assertSee('is not approved for pickup');
    }

    public function test_scan_student_loads_profile_for_checkout(): void
    {
        Livewire::actingAs($this->librarian)
            ->test(CheckOutBook::class)
            ->call('handleSearchCode', '2026-0001')
            ->assertSet('scannedMember.school_id', '2026-0001')
            ->assertSet('scannedMember.name', 'John Doe');
    }

    public function test_scan_available_book_adds_to_checkout_queue(): void
    {
        Livewire::actingAs($this->librarian)
            ->test(CheckOutBook::class)
            ->call('handleSearchCode', '2026-0001')
            ->call('handleSearchCode', 'BAR-999')
            ->assertCount('checkoutBooks', 1)
            ->assertSet('checkoutBooks.0.accession', 'ACC-999');
    }

    public function test_cannot_checkout_unavailable_book(): void
    {
        $this->book->update(['status' => 'borrowed']);

        $component = Livewire::actingAs($this->librarian)
            ->test(CheckOutBook::class)
            ->call('handleSearchCode', '2026-0001')
            ->call('handleSearchCode', 'BAR-999');

        $component->assertCount('checkoutBooks', 0);
        $this->assertStringContainsString('not available', $component->get('errorMessage'));
    }

    public function test_remove_book_from_checkout_queue(): void
    {
        Livewire::actingAs($this->librarian)
            ->test(CheckOutBook::class)
            ->call('handleSearchCode', '2026-0001')
            ->call('handleSearchCode', 'BAR-999')
            ->assertCount('checkoutBooks', 1)
            ->call('removeCheckoutBook', 'ACC-999')
            ->assertCount('checkoutBooks', 0);
    }

    public function test_confirm_checkout_transaction_creates_records(): void
    {
        Livewire::actingAs($this->librarian)
            ->test(CheckOutBook::class)
            ->call('handleSearchCode', '2026-0001')
            ->call('handleSearchCode', 'BAR-999')
            ->call('confirmCheckout')
            ->assertHasNoErrors()
            ->assertDispatched('checkout-completed', redirectUrl: route('librarian.circulation-desk.index'));

        $this->assertDatabaseHas('borrowing_transactions', [
            'book_id' => $this->book->id,
            'school_id' => $this->student->id,
            'return_date' => null
        ]);

        $this->assertEquals('borrowed', $this->book->fresh()->status);
    }

    public function test_checkout_is_blocked_when_a_lost_case_is_reported_after_the_queue_was_loaded(): void
    {
        $component = Livewire::actingAs($this->librarian)->test(CheckOutBook::class)
            ->call('handleSearchCode', '2026-0001')->call('handleSearchCode', 'BAR-999');
        BorrowingTransaction::create([
            'book_id' => $this->book->id, 'school_id' => $this->student->id,
            'issued_by_id' => $this->librarian->librarian->id,
            'borrow_type_id' => \App\Models\BorrowType::firstOrCreate(['name' => 'Regular Check-out'])->id,
            'issued_condition_id' => 2, 'issued_date' => now(), 'due_date' => now()->addDay(), 'lost_at' => now(),
        ]);
        $component->call('confirmCheckout')->assertDispatched('checkout-failed')->assertNotDispatched('checkout-completed');
        $this->assertSame('available', $this->book->fresh()->status);
    }

    public function test_librarian_can_report_and_resolve_a_lost_loan_from_the_return_screen(): void
    {
        BookCondition::firstOrCreate(['status' => 'Lost']);
        Livewire::actingAs($this->librarian)->test(CheckOutBook::class)
            ->call('handleSearchCode', '2026-0001')->call('handleSearchCode', 'BAR-999')
            ->call('confirmCheckout')->assertDispatched('checkout-completed');
        $loan = BorrowingTransaction::where('book_id', $this->book->id)->firstOrFail();
        $return = Livewire::actingAs($this->librarian)->test(\App\Livewire\Pages\Dashboard\CheckInBook::class)
            ->call('handleSearchCode', '2026-0001')
            ->call('openLostReport', $loan->id)->assertSet('lostModalMode', 'report')
            ->set('lostNotes.'.$loan->id, 'Student reported it missing')
            ->call('reportLost', $loan->id);
        $this->assertNotNull($loan->fresh()->lost_at);
        $this->assertSame('lost', $this->book->fresh()->status);
        $return->call('openLostCase', $loan->id)->assertSet('lostModalMode', 'resolve')
            ->set('resolutionNotes.'.$loan->id, 'Case settled')
            ->call('resolveLost', $loan->id);
        $this->assertNotNull($loan->fresh()->lost_resolved_at);
        $this->assertNull($loan->fresh()->return_date);
        $this->assertSame('lost', $this->book->fresh()->status);
        $return->call('handleSearchCode', 'ACC-999')->assertSet('lostModalMode', 'found')
            ->set('resolutionNotes.'.$loan->id, 'Original copy found')
            ->call('resolveLost', $loan->id)->assertSet('lostModalError', 'Select the recovered book condition.')
            ->set('recoveredConditions.'.$loan->id, 2)
            ->call('resolveLost', $loan->id)->assertSet('lostModalMode', '');
        $this->assertNotNull($loan->fresh()->return_date);
        $this->assertSame('available', $this->book->fresh()->status);
    }

    public function test_lost_copy_reassigns_an_approved_reservation_to_another_copy(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        [$loan, $reservation] = $this->lostReservationFixture();
        $other = $this->book->replicate();
        $other->accession_number = 'ACC-OTHER'; $other->code = 'BAR-OTHER'; $other->status = 'available'; $other->save();
        $deadline = $reservation->due_date->toDateTimeString();
        Livewire::actingAs($this->librarian)->withQueryParams(['loan' => $loan->id])->test(\App\Livewire\Pages\Dashboard\CheckInBook::class)
            ->call('openLostReport', $loan->id)->assertSee('1 active reservation');
        $result = app(\App\Services\LostBookService::class)->report($this->student->id, $loan->id, $this->librarian->librarian, 'Reported missing');
        $this->assertSame(['reassigned'], $result);
        $this->assertDatabaseHas('book_reservations', ['id' => $reservation->id, 'book_id' => $other->id, 'reservation_status_id' => 2, 'due_date' => $deadline]);
        $this->assertSame('reserved', $other->fresh()->status);
        $this->assertSame('lost', $this->book->fresh()->status);
        \Illuminate\Support\Facades\Notification::assertSentTo($this->student->account, \App\Notifications\Alerts::class);
    }

    public function test_lost_last_copy_cancels_reservation_and_preserves_request_time_on_paused_waitlist(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        [$loan, $reservation] = $this->lostReservationFixture();
        $result = app(\App\Services\LostBookService::class)->report($this->student->id, $loan->id, $this->librarian->librarian, 'Reported missing');
        $this->assertSame(['waitlisted'], $result);
        $this->assertDatabaseHas('book_reservations', ['id' => $reservation->id, 'reservation_status_id' => 4]);
        $this->assertDatabaseHas('reservation_waitlist', ['account_id' => $this->student->account_id, 'book_reservation_id' => $reservation->id, 'status' => 'lost_unavailable', 'created_at' => $reservation->reservation_date->toDateTimeString()]);
        \Illuminate\Support\Facades\Notification::assertSentTo($this->student->account, \App\Notifications\Alerts::class);
        $other = $this->book->replicate();
        $other->accession_number = 'ACC-OTHER'; $other->code = 'BAR-OTHER'; $other->status = 'available'; $other->save();
        app(\App\Services\ReservationWaitlistService::class)->reopen($this->book->book_detail_id);
        $newReservation = \App\Models\BookReservation::where('book_id', $other->id)->where('student_id', $this->student->id)->firstOrFail();
        $this->assertSame(1, (int) $newReservation->reservation_status_id);
        $this->assertTrue($newReservation->due_date->isFuture());
    }

    public function test_older_waiter_gets_available_copy_before_affected_reservation(): void
    {
        [$loan, $reservation] = $this->lostReservationFixture();
        $other = $this->book->replicate();
        $other->accession_number = 'ACC-OTHER'; $other->code = 'BAR-OTHER'; $other->status = 'available'; $other->save();
        $account = Account::factory()->create(['role_id' => 4, 'status_id' => 1]);
        $waiter = Student::create(['account_id' => $account->id, 'school_id_number' => 'WAIT-001', 'first_name' => 'Waiting', 'last_name' => 'Student', 'library_status_id' => 1]);
        $entry = new \App\Models\ReservationWaitlist(['book_detail_id' => $this->book->book_detail_id, 'account_id' => $account->id, 'student_id' => $waiter->id]);
        $entry->created_at = $reservation->reservation_date->copy()->subHour(); $entry->save();
        $result = app(\App\Services\LostBookService::class)->report($this->student->id, $loan->id, $this->librarian->librarian, 'Reported missing');
        $this->assertSame(['waitlisted'], $result);
        $this->assertDatabaseHas('book_reservations', ['book_id' => $other->id, 'student_id' => $waiter->id, 'reservation_status_id' => 1]);
        $this->assertDatabaseHas('reservation_waitlist', ['account_id' => $this->student->account_id, 'book_reservation_id' => $reservation->id, 'status' => 'waiting']);
    }

    public function test_lost_report_rolls_back_reservation_changes_if_fine_cannot_be_created(): void
    {
        [$loan, $reservation] = $this->lostReservationFixture();
        \Illuminate\Support\Facades\DB::table('fine_types')->where('name', 'Overdue')->update(['name' => 'Unavailable']);
        $this->mock(\App\Services\FineService::class)->shouldReceive('overdueAmount')->andReturn(100);
        try {
            app(\App\Services\LostBookService::class)->report($this->student->id, $loan->id, $this->librarian->librarian, 'Reported missing');
            $this->fail('The report must fail if the overdue fine type is not configured.');
        } catch (\DomainException $e) {
            $this->assertSame('The Overdue fine type is not configured.', $e->getMessage());
        }
        $this->assertNull($loan->fresh()->lost_at);
        $this->assertSame('borrowed', $this->book->fresh()->status);
        $this->assertDatabaseHas('book_reservations', ['id' => $reservation->id, 'reservation_status_id' => 2]);
        $this->assertDatabaseMissing('reservation_waitlist', ['book_reservation_id' => $reservation->id]);
    }

    private function lostReservationFixture(): array
    {
        BookCondition::firstOrCreate(['status' => 'Lost']);
        $loan = BorrowingTransaction::create([
            'book_id' => $this->book->id, 'school_id' => $this->student->id,
            'issued_by_id' => $this->librarian->librarian->id,
            'borrow_type_id' => \App\Models\BorrowType::firstOrCreate(['name' => 'Regular Check-out'])->id,
            'issued_condition_id' => 2, 'issued_date' => now()->subDay(), 'due_date' => now()->addDay(),
        ]);
        $this->book->update(['status' => 'borrowed']);
        $reservation = \App\Models\BookReservation::create([
            'book_id' => $this->book->id, 'student_id' => $this->student->id, 'account_id' => $this->student->account_id,
            'reservation_status_id' => 2, 'reservation_date' => now()->subHour()->startOfSecond(), 'due_date' => now()->addDay()->startOfSecond(),
        ]);
        return [$loan, $reservation];
    }

    public function test_checkout_rejects_copy_with_active_loan_even_if_status_is_available(): void
    {
        Livewire::actingAs($this->librarian)
            ->test(CheckOutBook::class)
            ->call('handleSearchCode', '2026-0001')
            ->call('handleSearchCode', 'BAR-999')
            ->call('confirmCheckout')
            ->assertDispatched('checkout-completed');

        $this->book->fresh()->update(['status' => 'available']);

        Student::create([
            'account_id' => Account::factory()->create(['role_id' => 4])->id,
            'school_id_number' => '2026-0002',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'library_status_id' => 1,
            'program' => 'BSCS',
            'year_level' => '4',
        ]);

        Livewire::actingAs($this->librarian)
            ->test(CheckOutBook::class)
            ->call('handleSearchCode', '2026-0002')
            ->call('handleSearchCode', 'BAR-999')
            ->assertCount('checkoutBooks', 1)
            ->call('confirmCheckout')
            ->assertDispatched('checkout-failed')
            ->assertNotDispatched('checkout-completed');

        $this->assertSame(1, BorrowingTransaction::where('book_id', $this->book->id)->whereNull('return_date')->count());
    }
    public function test_staff_cannot_exceed_the_active_reservation_limit(): void
    {
        foreach ([1, 2, 1] as $i => $status) {
            $copy = Book::create([
                'book_detail_id' => $this->book->book_detail_id,
                'book_condition_id' => 2,
                'accession_number' => 'RES-'. $i,
                'code' => 'RES-'. $i,
                'status' => 'reserved',
                'location' => 'Shelf A',
            ]);
            \App\Models\BookReservation::create([
                'book_id' => $copy->id, 'student_id' => $this->student->id,
                'account_id' => $this->student->account_id, 'reservation_status_id' => $status,
                'reservation_date' => now(), 'due_date' => now()->addDay(),
            ]);
        }

        Livewire::actingAs($this->librarian)
            ->test(\App\Livewire\Pages\Reservation\AdminDashboard::class)
            ->set('createSelectedStudentId', $this->student->id)
            ->set('createSelectedBookId', $this->book->id)
            ->call('submitManualReservation')
            ->assertDispatched('toast', fn ($event, $params) => str_contains($params['message'], 'maximum limit of 3 active reservations'));

        $this->assertDatabaseMissing('book_reservations', ['book_id' => $this->book->id]);
        $this->assertSame('available', $this->book->fresh()->status);
    }

    public function test_staff_cannot_reserve_reference_only_issue(): void
    {
        $this->book->bookDetail->update(['catalog_kind' => 'journal']);
        $this->book->bookDetail->periodicalIssue()->create(['borrowable' => false]);
        Livewire::actingAs($this->librarian)
            ->test(\App\Livewire\Pages\Reservation\AdminDashboard::class)
            ->set('createSelectedStudentId', $this->student->id)
            ->set('createSelectedBookId', $this->book->id)
            ->call('submitManualReservation')
            ->assertDispatched('toast', fn ($event, $params) => str_contains($params['message'], 'library use only'));
        $this->assertDatabaseMissing('book_reservations', ['book_id' => $this->book->id]);
    }

    public function test_reference_issue_can_be_borrowed_inside_library_until_closing(): void
    {
        $this->travelTo(Carbon::parse('2026-10-30 10:00', 'Asia/Manila'));
        $this->book->bookDetail->periodicalIssue()->create(['borrowable' => false]);

        Livewire::actingAs($this->librarian)->test(CheckOutBook::class)
            ->set('loanLocation', 'inside_library')
            ->call('handleSearchCode', '2026-0001')
            ->call('handleSearchCode', 'BAR-999')
            ->call('confirmCheckout')
            ->assertDispatched('checkout-completed');

        $loan = BorrowingTransaction::where('book_id', $this->book->id)->firstOrFail();
        $this->assertTrue($loan->isInsideLibrary());
        $this->assertSame('2026-10-30 19:00', $loan->due_date->copy()->setTimezone('Asia/Manila')->format('Y-m-d H:i'));
    }

    public function test_reservation_blacklist_does_not_prevent_checkout(): void
    {
        $this->student->account->forceFill(['reservations_blocked' => true])->save();
        Livewire::actingAs($this->librarian)->test(CheckOutBook::class)
            ->call('handleSearchCode', '2026-0001')
            ->assertSet('scannedMember.reservations_blocked', true)
            ->call('handleSearchCode', 'BAR-999')
            ->call('confirmCheckout')
            ->assertDispatched('checkout-completed');
    }

    public function test_cancelling_fulfilled_reservation_does_not_release_borrowed_copy(): void
    {
        $this->book->update(['status' => 'borrowed']);
        $reservation = \App\Models\BookReservation::create([
            'book_id' => $this->book->id, 'student_id' => $this->student->id,
            'account_id' => $this->student->account_id, 'reservation_status_id' => 3,
            'reservation_date' => now()->subDay(), 'due_date' => now()->addDay(),
        ]);
        Livewire::actingAs($this->librarian)->test(\App\Livewire\Pages\Reservation\AdminDashboard::class)
            ->set('cancelReservationId', $reservation->id)
            ->call('confirmCancelReservation')
            ->assertDispatched('toast', fn ($event, $params) => str_contains($params['message'], 'Only active reservations'));
        $this->assertSame('borrowed', $this->book->fresh()->status);
        $this->assertSame(3, (int) $reservation->fresh()->reservation_status_id);
    }
    public function test_reservation_receipt_is_scoped_to_its_student(): void
    {
        $reservation = \App\Models\BookReservation::create([
            'book_id' => $this->book->id, 'student_id' => $this->student->id,
            'account_id' => $this->student->account_id, 'reservation_status_id' => 1,
            'reservation_date' => now(), 'due_date' => now()->addDay(),
        ]);
        $page = new \App\Livewire\Pages\Reservation\Confirmation();
        $page->reference = str_pad((string) $reservation->id, 5, '0', STR_PAD_LEFT);
        $this->actingAs($this->student->account);
        $this->assertSame($reservation->id, $page->reservation()?->id);
        $this->actingAs($this->librarian);
        $this->assertNull($page->reservation());
        $this->actingAs($this->student->account);
        $page->reference = 'not-a-number';
        $this->assertNull($page->reservation());
    }

    public function test_old_reservation_link_redirects_to_padded_reference(): void
    {
        $this->withoutMiddleware()->get('/student/reservations/confirmation/9')
            ->assertRedirect(route('student.reservations.confirmation', ['reservation' => '00009']));
    }

}
