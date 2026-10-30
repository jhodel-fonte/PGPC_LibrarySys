<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Book;
use App\Models\ReservationWaitlist;
use App\Notifications\Alerts;
use App\Services\ReservationWaitlistService;
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
        Schema::create('roles', fn (Blueprint $t) => $t->id());
        Schema::create('accounts', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('role_id')->nullable(); $t->unsignedBigInteger('status_id')->nullable(); $t->string('email')->nullable(); $t->boolean('reservations_blocked')->default(false); $t->softDeletes(); });
        Schema::create('students', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('account_id'); $t->softDeletes(); });
        Schema::create('book_datas', function (Blueprint $t) { $t->id(); $t->string('book_title'); $t->softDeletes(); });
        Schema::create('book_details', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('book_data_id'); $t->string('catalog_kind')->default('book'); $t->softDeletes(); });
        Schema::create('periodical_issues', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('book_detail_id'); $t->boolean('borrowable')->default(true); $t->integer('loan_days')->nullable(); $t->timestamps(); });
        Schema::create('books', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('book_detail_id'); $t->string('status'); $t->timestamps(); $t->softDeletes(); });
        Schema::create('book_reservations', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('book_id'); $t->unsignedBigInteger('student_id'); $t->unsignedBigInteger('account_id'); $t->unsignedBigInteger('reservation_status_id'); $t->dateTime('reservation_date'); $t->dateTime('due_date'); $t->dateTime('approved_date')->nullable(); $t->dateTime('cancelled_date')->nullable(); $t->text('comment')->nullable(); $t->timestamps(); $t->softDeletes(); });
        Schema::create('reservation_waitlist', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('book_detail_id'); $t->unsignedBigInteger('account_id'); $t->unsignedBigInteger('student_id'); $t->string('status')->default('waiting'); $t->unsignedBigInteger('book_reservation_id')->nullable(); $t->timestamps(); });
        DB::table('accounts')->insert([['id' => 1, 'status_id' => 1, 'email' => 'one@example.com'], ['id' => 2, 'status_id' => 1, 'email' => 'two@example.com']]);
        DB::table('students')->insert([['id' => 1, 'account_id' => 1], ['id' => 2, 'account_id' => 2]]);
        DB::table('book_datas')->insert(['id' => 1, 'book_title' => 'Example']);
        DB::table('book_details')->insert(['id' => 1, 'book_data_id' => 1]);
        DB::table('books')->insert(['id' => 1, 'book_detail_id' => 1, 'status' => 'borrowed']);
        Notification::fake();
    }

    public function test_released_copy_goes_to_first_waiting_student(): void
    {
        $service = app(ReservationWaitlistService::class);
        $service->join(1, Account::findOrFail(1));
        $service->join(1, Account::findOrFail(2));
        Book::findOrFail(1)->update(['status' => 'available']);

        $this->assertSame('reserved', Book::findOrFail(1)->status);
        $this->assertDatabaseHas('book_reservations', ['book_id' => 1, 'account_id' => 1, 'reservation_status_id' => 2]);
        $this->assertDatabaseHas('reservation_waitlist', ['account_id' => 1, 'status' => 'allocated']);
        $this->assertDatabaseHas('reservation_waitlist', ['account_id' => 2, 'status' => 'waiting']);
        Notification::assertSentTo(Account::findOrFail(1), Alerts::class);
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
        $this->assertDatabaseHas('book_reservations', ['book_id' => 1, 'account_id' => 1, 'reservation_status_id' => 2]);
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
        $this->assertDatabaseHas('book_reservations', ['book_id' => 1, 'account_id' => 2, 'reservation_status_id' => 2]);
    }
}
