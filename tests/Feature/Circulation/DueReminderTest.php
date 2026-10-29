<?php

namespace Tests\Feature\Circulation;

use App\Jobs\SendSmsJob;
use App\Models\Account;
use App\Notifications\Alerts;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DueReminderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('accounts', function (Blueprint $table) {
            $table->id(); $table->string('email')->nullable(); $table->unsignedBigInteger('role_id')->nullable(); $table->softDeletes();
        });
        Schema::create('roles', function (Blueprint $table) { $table->id(); });
        Schema::create('students', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('account_id')->nullable(); $table->string('contact_num')->nullable(); $table->softDeletes();
        });
        Schema::create('book_datas', function (Blueprint $table) { $table->id(); $table->string('book_title'); $table->softDeletes(); });
        Schema::create('book_details', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('book_data_id'); $table->softDeletes();
        });
        Schema::create('books', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('book_detail_id'); $table->softDeletes();
        });
        Schema::create('borrowing_transactions', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('book_id'); $table->unsignedBigInteger('school_id');
            $table->dateTime('due_date'); $table->dateTime('return_date')->nullable();
            $table->dateTime('due_reminder_for')->nullable(); $table->timestamps();
        });
        DB::table('accounts')->insert(['id' => 1, 'email' => 'student@example.com']);
        DB::table('students')->insert(['id' => 1, 'account_id' => 1, 'contact_num' => '09171234567']);
        DB::table('book_datas')->insert(['id' => 1, 'book_title' => 'Test Book']);
        DB::table('book_details')->insert(['id' => 1, 'book_data_id' => 1]);
        DB::table('books')->insert(['id' => 1, 'book_detail_id' => 1]);
        $this->travelTo(now()->startOfDay()->setHour(9));
    }

    public function test_reminders_respect_window_returns_and_due_date_changes(): void
    {
        Notification::fake();
        config(['settings.notifications.due_reminder_days' => 2]);
        foreach ([1 => 2, 2 => 3, 3 => -1, 4 => 1] as $id => $days) {
            DB::table('borrowing_transactions')->insert([
                'id' => $id, 'book_id' => 1, 'school_id' => 1,
                'due_date' => now()->addDays($days), 'return_date' => $id === 4 ? now() : null,
            ]);
        }

        $this->artisan('loans:send-due-reminders')->assertSuccessful();
        $this->artisan('loans:send-due-reminders')->assertSuccessful();
        $account = Account::findOrFail(1);
        Notification::assertSentToTimes($account, Alerts::class, 1);
        Notification::assertSentTo($account, Alerts::class, fn (Alerts $alert) =>
            $alert->bookTitle === 'Test Book' && $alert->sendSms && $alert->via($account) === ['database', 'mail']
        );

        DB::table('borrowing_transactions')->where('id', 1)->update(['due_date' => now()->addDay()]);
        $this->artisan('loans:send-due-reminders')->assertSuccessful();
        Notification::assertSentToTimes($account, Alerts::class, 2);
    }

    public function test_database_alert_queues_sms_with_book_and_due_date(): void
    {
        Queue::fake();
        $alert = new Alerts(title: 'Book return reminder', message: 'Please return your book by Nov 01, 2026.', bookTitle: 'Test Book', sendSms: true);
        $data = $alert->toDatabase(Account::findOrFail(1));
        $this->assertSame('Test Book', $data['book_title']);
        Queue::assertPushed(SendSmsJob::class, fn (SendSmsJob $job) =>
            $job->phoneNumber === '09171234567' && str_contains($job->message, 'Test Book') && str_contains($job->message, 'Nov 01, 2026')
        );
    }
}
