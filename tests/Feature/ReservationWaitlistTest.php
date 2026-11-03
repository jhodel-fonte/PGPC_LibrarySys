<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Book;
use App\Models\ReservationWaitlist;
use App\Notifications\Alerts;
use App\Services\ReservationWaitlistService;
use App\Services\ReservationApprovalService;
use App\Services\ReservationSettings;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReservationWaitlistTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('roles', function (Blueprint $t) { $t->id(); $t->string('name')->nullable(); });
        Schema::create('permissions', function (Blueprint $t) { $t->id(); $t->string('name'); });
        Schema::create('account_permissions', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('account_id'); $t->unsignedBigInteger('permission_id'); $t->boolean('is_allowed'); $t->timestamps(); });
        Schema::create('role_permissions', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('role_id'); $t->unsignedBigInteger('permission_id'); $t->boolean('is_allowed'); });
        Schema::create('reservation_statuses', function (Blueprint $t) { $t->id(); $t->string('status'); });
        Schema::create('accounts', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('role_id')->nullable(); $t->unsignedBigInteger('status_id')->nullable(); $t->string('email')->nullable(); $t->boolean('reservations_blocked')->default(false); $t->softDeletes(); });
        Schema::create('librarians', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('account_id'); $t->softDeletes(); });
        (require database_path('migrations/2026_11_01_000002_create_audit_logs_table.php'))->up();
        (require database_path('migrations/2026_11_01_000003_add_action_context_to_audit_logs.php'))->up();
        Schema::create('students', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('account_id'); $t->softDeletes(); });
        Schema::create('book_datas', function (Blueprint $t) { $t->id(); $t->string('book_title'); $t->softDeletes(); });
        Schema::create('book_details', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('book_data_id'); $t->string('catalog_kind')->default('book'); $t->softDeletes(); });
        Schema::create('periodical_issues', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('book_detail_id'); $t->boolean('borrowable')->default(true); $t->integer('loan_days')->nullable(); $t->timestamps(); });
        Schema::create('books', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('book_detail_id'); $t->string('status'); $t->timestamps(); $t->softDeletes(); });
        Schema::create('book_reservations', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('book_id'); $t->unsignedBigInteger('student_id'); $t->unsignedBigInteger('account_id'); $t->unsignedBigInteger('reservation_status_id'); $t->dateTime('reservation_date'); $t->dateTime('due_date'); $t->dateTime('approved_date')->nullable(); $t->dateTime('cancelled_date')->nullable(); $t->text('comment')->nullable(); $t->timestamps(); $t->softDeletes(); });
        Schema::create('reservation_waitlist', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('book_detail_id'); $t->unsignedBigInteger('account_id'); $t->unsignedBigInteger('student_id'); $t->string('status')->default('waiting'); $t->unsignedBigInteger('book_reservation_id')->nullable(); $t->timestamps(); });
        Schema::create('borrowing_transactions', function (Blueprint $t) {
            $t->id(); $t->unsignedBigInteger('school_id'); $t->unsignedBigInteger('book_id'); $t->unsignedBigInteger('borrow_type_id');
            $t->dateTime('due_date'); $t->dateTime('return_date')->nullable(); $t->dateTime('lost_at')->nullable();
            $t->unsignedBigInteger('lost_reported_by_id')->nullable(); $t->text('lost_note')->nullable();
            $t->dateTime('lost_resolved_at')->nullable(); $t->unsignedBigInteger('lost_resolved_by_id')->nullable(); $t->text('lost_resolution_note')->nullable();
            $t->unsignedBigInteger('received_by_id')->nullable(); $t->unsignedBigInteger('return_condition_id')->nullable(); $t->text('return_condition_note')->nullable(); $t->timestamps();
        });
        (require database_path('migrations/2026_10_28_111951_create_notifications_table.php'))->up();
        (require database_path('migrations/2026_11_01_000004_create_reservation_notification_deliveries.php'))->up();
        (require database_path('migrations/2026_11_01_000005_add_notification_query_indexes.php'))->up();
        DB::table('accounts')->insert([['id' => 1, 'status_id' => 1, 'email' => 'one@example.com'], ['id' => 2, 'status_id' => 1, 'email' => 'two@example.com']]);
        DB::table('students')->insert([['id' => 1, 'account_id' => 1], ['id' => 2, 'account_id' => 2]]);
        DB::table('book_datas')->insert(['id' => 1, 'book_title' => 'Example']);
        DB::table('book_details')->insert(['id' => 1, 'book_data_id' => 1]);
        DB::table('books')->insert(['id' => 1, 'book_detail_id' => 1, 'status' => 'borrowed']);
        Notification::fake();
    }

    public function test_lost_report_and_resolution_preserve_loan_and_copy_history(): void
    {
        Schema::table('books', fn (Blueprint $t) => $t->unsignedBigInteger('book_condition_id')->nullable());
        Schema::create('book_conditions', function (Blueprint $t) { $t->id(); $t->string('status'); });
        Schema::create('borrow_types', function (Blueprint $t) { $t->id(); $t->string('name'); });
        DB::table('book_conditions')->insert([['id' => 1, 'status' => 'Good'], ['id' => 5, 'status' => 'Lost']]);
        DB::table('borrow_types')->insert(['id' => 1, 'name' => 'Regular Check-out']);
        DB::table('borrowing_transactions')->insert(['id' => 1, 'school_id' => 1, 'book_id' => 1, 'borrow_type_id' => 1, 'due_date' => now()->addDay()]);
        $librarian = new \App\Models\Librarian(); $librarian->id = 1;
        $service = app(\App\Services\LostBookService::class);
        $service->report(1, 1, $librarian, 'Student reported it missing');
        $this->assertDatabaseHas('books', ['id' => 1, 'status' => 'lost', 'book_condition_id' => 5]);
        $this->assertDatabaseHas('borrowing_transactions', ['id' => 1, 'return_date' => null, 'lost_resolved_at' => null]);
        $service->resolve(1, 1, $librarian, 'Case settled');
        $this->assertDatabaseHas('books', ['id' => 1, 'status' => 'lost']);
        $this->assertDatabaseHas('borrowing_transactions', ['id' => 1, 'return_date' => null, 'lost_resolution_note' => 'Case settled']);
        $service->resolve(1, 1, $librarian, 'Book found', 1);
        $loan = \App\Models\BorrowingTransaction::findOrFail(1);
        $this->assertNotNull($loan->lost_at);
        $this->assertNotNull($loan->lost_resolved_at);
        $this->assertNotNull($loan->return_date);
        $this->assertSame('Case settled', $loan->lost_resolution_note);
        $this->assertDatabaseHas('books', ['id' => 1, 'status' => 'available', 'book_condition_id' => 1]);
    }

    public function test_lost_copy_suspends_and_reopens_waitlist_in_original_order(): void
    {
        $service = app(ReservationWaitlistService::class);
        $service->join(1, Account::findOrFail(1));
        $service->join(1, Account::findOrFail(2));
        DB::table('books')->where('id', 1)->update(['status' => 'lost']);
        DB::transaction(fn () => $service->suspendUnavailable(1));
        $this->assertSame(['lost_unavailable', 'lost_unavailable'], ReservationWaitlist::orderBy('id')->pluck('status')->all());
        DB::table('books')->where('id', 1)->update(['status' => 'borrowed']);
        $service->reopen(1);
        $this->assertSame([1, 2], ReservationWaitlist::where('status', 'waiting')->orderBy('id')->pluck('account_id')->all());
    }

    public function test_released_copy_goes_to_first_waiting_student(): void
    {
        $service = app(ReservationWaitlistService::class);
        $service->join(1, Account::findOrFail(1));
        $service->join(1, Account::findOrFail(2));
        $this->assertDatabaseCount('book_reservations', 0);
        Book::findOrFail(1)->update(['status' => 'available']);

        $this->assertSame('reserved', Book::findOrFail(1)->status);
        $this->assertDatabaseHas('book_reservations', ['book_id' => 1, 'account_id' => 1, 'reservation_status_id' => 1, 'approved_date' => null]);
        $this->assertDatabaseHas('reservation_waitlist', ['account_id' => 1, 'status' => 'allocated']);
        $this->assertDatabaseHas('reservation_waitlist', ['account_id' => 2, 'status' => 'waiting']);
        $reservationLog = DB::table('audit_logs')->where('action', 'book_reservation.created')->first();
        $this->assertNotNull($reservationLog->group_id);
        $this->assertDatabaseHas('audit_logs', ['group_id' => $reservationLog->group_id, 'action' => 'book.updated']);
        Notification::assertSentTo(Account::findOrFail(1), Alerts::class);
    }

    public function test_staff_notification_is_unique_and_rolls_back_with_reservation(): void
    {
        DB::table('roles')->insert(['id' => 1, 'name' => 'Admin']);
        DB::table('accounts')->insert(['id' => 3, 'role_id' => 1, 'status_id' => 1]);
        DB::table('book_reservations')->insert([
            'id' => 10, 'book_id' => 1, 'student_id' => 1, 'account_id' => 1,
            'reservation_status_id' => 1, 'reservation_date' => now(), 'due_date' => now()->addDay(),
        ]);
        $reservation = \App\Models\BookReservation::findOrFail(10);
        $service = app(\App\Services\ReservationNotificationService::class);
        DB::transaction(function () use ($service, $reservation) {
            $service->notifyStaff($reservation);
            $service->notifyStaff($reservation);
        });
        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('reservation_notification_deliveries', ['reservation_id' => 10, 'account_id' => 3]);
        DB::table('notifications')->delete();
        $service->notifyStaff($reservation);
        $this->assertDatabaseCount('notifications', 0);

        DB::table('book_reservations')->where('id', 10)->update(['id' => 11]);
        $reservation->id = 11;
        DB::beginTransaction();
        $service->notifyStaff($reservation);
        DB::rollBack();
        $this->assertDatabaseMissing('reservation_notification_deliveries', ['reservation_id' => 11, 'account_id' => 3]);
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_only_active_authorized_staff_receive_notification(): void
    {
        DB::table('roles')->insert([['id' => 1, 'name' => 'Admin'], ['id' => 2, 'name' => 'Librarian']]);
        DB::table('permissions')->insert(['id' => 1, 'name' => 'view_circulation']);
        DB::table('role_permissions')->insert(['role_id' => 2, 'permission_id' => 1, 'is_allowed' => true]);
        DB::table('accounts')->insert([
            ['id' => 3, 'role_id' => 1, 'status_id' => 1],
            ['id' => 4, 'role_id' => 2, 'status_id' => 1],
            ['id' => 5, 'role_id' => 2, 'status_id' => 2],
            ['id' => 6, 'role_id' => 2, 'status_id' => 1],
        ]);
        DB::table('account_permissions')->insert(['account_id' => 6, 'permission_id' => 1, 'is_allowed' => false]);
        DB::table('book_reservations')->insert([
            'id' => 10, 'book_id' => 1, 'student_id' => 1, 'account_id' => 1,
            'reservation_status_id' => 1, 'reservation_date' => now(), 'due_date' => now()->addDay(),
        ]);
        app(\App\Services\ReservationNotificationService::class)->notifyStaff(\App\Models\BookReservation::findOrFail(10), 3);
        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('reservation_notification_deliveries', ['reservation_id' => 10, 'account_id' => 4]);
    }

    public function test_staff_can_only_mark_their_own_notification_read(): void
    {
        DB::table('roles')->insert(['id' => 1, 'name' => 'Admin']);
        DB::table('accounts')->insert([['id' => 3, 'role_id' => 1, 'status_id' => 1], ['id' => 4, 'role_id' => 1, 'status_id' => 1]]);
        DB::table('book_reservations')->insert([
            'id' => 10, 'book_id' => 1, 'student_id' => 1, 'account_id' => 1,
            'reservation_status_id' => 1, 'reservation_date' => now(), 'due_date' => now()->addDay(),
        ]);
        app(\App\Services\ReservationNotificationService::class)->notifyStaff(\App\Models\BookReservation::findOrFail(10));
        $own = DB::table('notifications')->where('notifiable_id', 3)->value('id');
        $other = DB::table('notifications')->where('notifiable_id', 4)->value('id');
        $this->actingAs(Account::findOrFail(3));
        \Livewire\Livewire::test(\App\Livewire\Components\Notification\NotificationContent::class)
            ->call('markAsRead', $other)->call('markAsRead', $own);
        $this->assertNotNull(DB::table('notifications')->where('id', $own)->value('read_at'));
        $this->assertNull(DB::table('notifications')->where('id', $other)->value('read_at'));
    }

    public function test_notification_history_uses_stable_pages_and_scoped_mark_all(): void
    {
        $this->actingAs(Account::findOrFail(1));
        for ($i = 1; $i <= 32; $i++) {
            DB::table('notifications')->insert([
                'id' => sprintf('00000000-0000-4000-8000-%012d', $i), 'type' => Alerts::class,
                'notifiable_type' => Account::class, 'notifiable_id' => $i === 32 ? 2 : 1,
                'data' => json_encode(['title' => 'Notice']), 'created_at' => '2026-01-01 00:00:00', 'updated_at' => now(),
            ]);
        }
        $component = new \App\Livewire\Components\Notification\NotificationContent();
        $first = $component->page();
        $second = $component->page($first['cursor']);
        $third = $component->page($second['cursor']);
        $ids = array_column([...$first['items'], ...$second['items'], ...$third['items']], 'id');
        $this->assertCount(31, array_unique($ids));
        $this->assertNull($third['cursor']);
        $this->assertSame(0, $component->markAllAsRead());
        $this->assertSame(1, DB::table('notifications')->where('notifiable_id', 2)->whereNull('read_at')->count());
    }

    public function test_a_student_cannot_join_the_same_waitlist_twice(): void
    {
        $service = app(ReservationWaitlistService::class);
        $account = Account::findOrFail(1);
        $service->join(1, $account);
        $this->expectExceptionMessage('already on the waitlist');
        $service->join(1, $account);
    }

    public function test_blocked_student_is_skipped_and_can_be_allocated_when_unblocked(): void
    {
        $service = app(ReservationWaitlistService::class);
        $service->join(1, Account::findOrFail(1));
        $service->join(1, Account::findOrFail(2));
        DB::table('accounts')->where('id', 1)->update(['reservations_blocked' => true]);
        Book::findOrFail(1)->update(['status' => 'available']);
        $this->assertDatabaseHas('book_reservations', ['book_id' => 1, 'account_id' => 2]);
        $this->assertDatabaseHas('reservation_waitlist', ['account_id' => 1, 'status' => 'waiting']);
        DB::table('accounts')->where('id', 1)->update(['reservations_blocked' => false]);
        DB::table('books')->insert(['id' => 2, 'book_detail_id' => 1, 'status' => 'available']);
        $service->allocate(1);
        $this->assertDatabaseHas('book_reservations', ['book_id' => 2, 'account_id' => 1]);
    }

    public function test_blocked_waiter_does_not_block_other_reservations(): void
    {
        $service = app(ReservationWaitlistService::class);
        $service->join(1, Account::findOrFail(1));
        DB::table('accounts')->where('id', 1)->update(['reservations_blocked' => true]);
        $this->assertFalse($service->hasEligibleWaiter(1));
        DB::table('accounts')->where('id', 1)->update(['reservations_blocked' => false]);
        $this->assertTrue($service->hasEligibleWaiter(1));
    }

    public function test_physical_item_is_automatically_reserved_when_returned(): void
    {
        DB::table('book_details')->where('id', 1)->update(['catalog_kind' => 'equipment']);
        app(ReservationWaitlistService::class)->join(1, Account::findOrFail(1));
        Book::findOrFail(1)->update(['status' => 'available']);
        $this->assertDatabaseHas('book_reservations', ['book_id' => 1, 'account_id' => 1, 'reservation_status_id' => 1]);
        Notification::assertSentTo(Account::findOrFail(1), Alerts::class, fn (Alerts $alert) => $alert->sendSms && in_array('mail', $alert->via(Account::findOrFail(1))));
    }

    public function test_blocked_account_cannot_join_queue(): void
    {
        DB::table('accounts')->where('id', 1)->update(['reservations_blocked' => true]);
        $this->expectException(\RuntimeException::class);
        app(ReservationWaitlistService::class)->join(1, Account::findOrFail(1));
    }

    public function test_expired_reservation_is_reallocated_to_waiting_student(): void
    {
        $service = app(ReservationWaitlistService::class);
        $service->join(1, Account::findOrFail(2));
        DB::table('books')->where('id', 1)->update(['status' => 'reserved']);
        DB::table('book_reservations')->insert(['id' => 1, 'book_id' => 1, 'student_id' => 1, 'account_id' => 1,
            'reservation_status_id' => 2, 'reservation_date' => now()->subDays(4), 'due_date' => now()->subMinute()]);
        $service->expireReservations();
        $this->assertDatabaseHas('book_reservations', ['id' => 1, 'reservation_status_id' => 5]);
        $this->assertDatabaseHas('book_reservations', ['book_id' => 1, 'account_id' => 2, 'reservation_status_id' => 1]);
    }

    public function test_automatic_approval_respects_setting_delay_deadline_and_blacklist(): void
    {
        $this->travelTo(now()->setTime(10, 0));
        app(ReservationWaitlistService::class)->join(1, Account::findOrFail(1));
        Book::findOrFail(1)->update(['status' => 'available']);
        $reservation = \App\Models\BookReservation::firstOrFail();
        $deadline = $reservation->due_date->toDateTimeString();
        $approval = app(ReservationApprovalService::class);

        config(['settings.reservation.auto_approve' => true, 'settings.reservation.auto_approve_delay_minutes' => 60]);
        $approval->approveDue();
        $this->assertSame(1, (int) $reservation->fresh()->reservation_status_id);
        config(['settings.reservation.auto_approve' => false]);
        $this->travel(61)->minutes();
        $approval->approveDue();
        $this->assertSame(1, (int) $reservation->fresh()->reservation_status_id);

        config(['settings.reservation.auto_approve' => true]);
        DB::table('accounts')->where('id', 1)->update(['reservations_blocked' => true]);
        $approval->approveDue();
        $this->assertSame(1, (int) $reservation->fresh()->reservation_status_id);
        DB::table('accounts')->where('id', 1)->update(['reservations_blocked' => false]);
        $approval->approveDue();
        $this->assertSame(2, (int) $reservation->fresh()->reservation_status_id);
        $this->assertSame($deadline, $reservation->fresh()->due_date->toDateTimeString());
    }

    public function test_automatic_approval_waits_when_student_has_overdue_loan(): void
    {
        app(ReservationWaitlistService::class)->join(1, Account::findOrFail(1));
        Book::findOrFail(1)->update(['status' => 'available']);
        $reservation = \App\Models\BookReservation::firstOrFail();
        DB::table('borrowing_transactions')->insert([
            'school_id' => 1, 'book_id' => 1, 'borrow_type_id' => 1, 'due_date' => now()->subDay(),
        ]);
        config(['settings.reservation.auto_approve' => true, 'settings.reservation.auto_approve_delay_minutes' => 0]);

        app(ReservationApprovalService::class)->approveDue();

        $this->assertSame(1, (int) $reservation->fresh()->reservation_status_id);
        DB::table('borrowing_transactions')->update(['return_date' => now()]);
        app(ReservationApprovalService::class)->approveDue();
        $this->assertSame(2, (int) $reservation->fresh()->reservation_status_id);
    }

    public function test_settings_fall_back_to_config_and_use_database_values_when_present(): void
    {
        config(['settings.reservation.max_pickup_days' => 3, 'settings.reservation.auto_approve' => false]);
        $this->assertSame(3, ReservationSettings::maxPickupDays());
        $this->assertFalse(ReservationSettings::autoApprove());
        Schema::create('system_settings', function (Blueprint $t) { $t->string('setting_key'); $t->text('setting_value')->nullable(); });
        DB::table('system_settings')->insert(['setting_key' => 'reservation.auto_approve', 'setting_value' => 'true']);
        $this->assertTrue(ReservationSettings::autoApprove());
        $this->assertSame(3, ReservationSettings::maxPickupDays());
    }

    public function test_expired_pending_reservation_cannot_be_approved_and_is_released(): void
    {
        app(ReservationWaitlistService::class)->join(1, Account::findOrFail(1));
        Book::findOrFail(1)->update(['status' => 'available']);
        $reservation = \App\Models\BookReservation::firstOrFail();
        $reservation->update(['due_date' => now()->subMinute()]);
        try {
            app(ReservationApprovalService::class)->approve($reservation->id);
            $this->fail('Expired reservations must not be approved.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('deadline', $e->getMessage());
        }
        app(ReservationWaitlistService::class)->expireReservations();
        $this->assertSame(5, (int) $reservation->fresh()->reservation_status_id);
        $this->assertSame('available', Book::findOrFail(1)->status);
    }
}
