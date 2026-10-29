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
        Schema::create('book_details', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('book_data_id'); $t->softDeletes(); });
        Schema::create('books', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('book_detail_id'); $t->string('status'); $t->timestamps(); $t->softDeletes(); });
        Schema::create('book_reservations', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('book_id'); $t->unsignedBigInteger('student_id'); $t->unsignedBigInteger('account_id'); $t->unsignedBigInteger('reservation_status_id'); $t->dateTime('reservation_date'); $t->dateTime('due_date'); $t->dateTime('approved_date')->nullable(); $t->text('comment')->nullable(); $t->timestamps(); $t->softDeletes(); });
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
}
