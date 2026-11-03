<?php

namespace Tests\Feature;

use App\Models\Librarian;
use App\Models\BookDetail;
use App\Services\FineService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FinePaymentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('fines', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('student_id'); $table->unsignedBigInteger('borrowing_transaction_id')->nullable(); $table->unsignedBigInteger('fine_type_id')->nullable(); $table->decimal('amount', 8, 2);
            $table->string('fine_status'); $table->timestamp('paid_at')->nullable(); $table->timestamps();
        });
        Schema::create('fine_payments', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('fine_id'); $table->unsignedBigInteger('received_by_id');
            $table->timestamp('payment_date'); $table->decimal('payment_amount', 8, 2); $table->string('receipt_number')->nullable(); $table->timestamps();
        });
        Schema::create('fine_types', function (Blueprint $table) { $table->id(); $table->string('name'); $table->timestamps(); });
        Schema::create('borrowing_transactions', function (Blueprint $table) { $table->id(); $table->timestamp('due_date'); $table->timestamp('return_date')->nullable(); $table->timestamp('lost_at')->nullable(); $table->timestamps(); });
        DB::table('fines')->insert(['id' => 1, 'student_id' => 7, 'amount' => 150, 'fine_status' => 'unpaid']);
    }

    public function test_confirmation_records_payment_once_and_clears_unpaid_status(): void
    {
        $librarian = new Librarian();
        $librarian->id = 3;
        $service = app(FineService::class);
        $service->confirmPayment(1, 7, $librarian);

        $this->assertDatabaseHas('fines', ['id' => 1, 'fine_status' => 'paid']);
        $this->assertNotNull(DB::table('fines')->where('id', 1)->value('paid_at'));
        $this->assertDatabaseHas('fine_payments', ['fine_id' => 1, 'received_by_id' => 3, 'payment_amount' => 150]);

        try {
            $service->confirmPayment(1, 7, $librarian);
            $this->fail('A paid fine cannot be confirmed again.');
        } catch (\DomainException $exception) {
            $this->assertSame(1, DB::table('fine_payments')->count());
        }
    }

    public function test_payment_cannot_be_applied_to_another_student(): void
    {
        $librarian = new Librarian();
        $librarian->id = 3;
        try {
            app(FineService::class)->confirmPayment(1, 8, $librarian);
            $this->fail('Another student’s fine must not be paid.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            $this->assertDatabaseHas('fines', ['id' => 1, 'fine_status' => 'unpaid']);
            $this->assertSame(0, DB::table('fine_payments')->count());
        }
    }

    public function test_overdue_amount_uses_calendar_days_and_configured_rate(): void
    {
        $this->travelTo(now()->startOfDay()->addHours(10));
        config(['settings.library_config.penalty_fee' => 50]);
        $service = app(FineService::class);
        $this->assertSame(0.0, $service->overdueAmount(now()->subHours(2)));
        $this->assertSame(150.0, $service->overdueAmount(now()->subDays(3)));
    }

    public function test_overdue_amount_uses_the_catalog_item_rate_when_set(): void
    {
        $this->travelTo(now()->startOfDay()->addHours(10));
        $detail = new BookDetail(['daily_fine' => 12.50]);

        $this->assertSame(37.5, app(FineService::class)->overdueAmount(now()->subDays(3), $detail));
    }
    public function test_payment_date_cuts_off_fine_and_records_receipt_once(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-03-08 12:00:00'));
        DB::table('fine_types')->insert(['id' => 1, 'name' => 'Overdue']);
        DB::table('borrowing_transactions')->insert(['id' => 1, 'due_date' => '2026-03-01 00:00:00', 'return_date' => '2026-03-08 00:00:00']);
        DB::table('fines')->where('id', 1)->update(['fine_type_id' => 1, 'borrowing_transaction_id' => 1, 'amount' => 350]);
        $librarian = new Librarian(); $librarian->id = 3;
        $service = app(FineService::class);
        $this->assertSame(['days' => 4, 'rate' => 50.0, 'amount' => 200.0], $service->paymentCalculation(\App\Models\Fine::find(1), '2026-03-05'));
        $service->confirmOverduePayment(1, 7, $librarian, '2026-03-05', 'OR-123');
        $this->assertDatabaseHas('fines', ['id' => 1, 'amount' => 200, 'fine_status' => 'paid']);
        $this->assertDatabaseHas('fine_payments', ['fine_id' => 1, 'payment_amount' => 200, 'receipt_number' => 'OR-123', 'payment_date' => '2026-03-05 00:00:00']);
        $this->expectException(\DomainException::class);
        $service->confirmOverduePayment(1, 7, $librarian, '2026-03-05', 'OR-123');
    }

    public function test_calculation_caps_at_return_and_rejects_unreturned_items(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-03-10'));
        DB::table('fine_types')->insert(['id' => 1, 'name' => 'Overdue']);
        DB::table('borrowing_transactions')->insert(['id' => 1, 'due_date' => '2026-03-01', 'return_date' => '2026-03-05']);
        DB::table('fines')->where('id', 1)->update(['fine_type_id' => 1, 'borrowing_transaction_id' => 1, 'amount' => 200]);
        $service = app(FineService::class);
        $this->assertSame(200.0, $service->paymentCalculation(\App\Models\Fine::find(1), '2026-03-08')['amount']);
        DB::table('borrowing_transactions')->where('id', 1)->update(['return_date' => null]);
        $this->expectException(\DomainException::class);
        $service->paymentCalculation(\App\Models\Fine::find(1), '2026-03-08');
    }
    public function test_lost_book_overdue_payment_is_capped_at_the_report_date_even_if_found_later(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-03-10'));
        DB::table('fine_types')->insert(['id' => 1, 'name' => 'Overdue']);
        DB::table('borrowing_transactions')->insert(['id' => 1, 'due_date' => '2026-03-01', 'lost_at' => '2026-03-05', 'return_date' => '2026-03-09']);
        DB::table('fines')->where('id', 1)->update(['fine_type_id' => 1, 'borrowing_transaction_id' => 1, 'amount' => 200]);
        $this->assertSame(200.0, app(FineService::class)->paymentCalculation(\App\Models\Fine::find(1), '2026-03-10')['amount']);
    }
    public function test_scanning_student_qr_selects_student_and_unknown_code_does_not_change_selection(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id(); $table->string('school_id_number')->nullable(); $table->softDeletes(); $table->timestamps();
        });
        DB::table('students')->insert(['id' => 7, 'school_id_number' => '2026-0001']);
        $admin = \Mockery::mock(\App\Models\Account::class)->makePartial();
        $admin->shouldReceive('hasRole')->with('Admin')->andReturn(true);
        \Illuminate\Support\Facades\Auth::shouldReceive('user')->andReturn($admin);
        $page = new \App\Livewire\Pages\Dashboard\ProcessFinePayment();
        $page->receiptNumber = 'OLD-RECEIPT';
        $page->scanStudent('2026-0001');
        $this->assertSame(7, $page->studentId);
        $this->assertSame('', $page->receiptNumber);
        $page->scanStudent('unknown');
        $this->assertSame(7, $page->studentId);
        $this->assertTrue($page->getErrorBag()->has('scan'));
    }
}
