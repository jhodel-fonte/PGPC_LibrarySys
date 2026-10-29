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
            $table->id(); $table->unsignedBigInteger('student_id'); $table->decimal('amount', 8, 2);
            $table->string('fine_status'); $table->timestamp('paid_at')->nullable(); $table->timestamps();
        });
        Schema::create('fine_payments', function (Blueprint $table) {
            $table->id(); $table->unsignedBigInteger('fine_id'); $table->unsignedBigInteger('received_by_id');
            $table->timestamp('payment_date'); $table->decimal('payment_amount', 8, 2); $table->timestamps();
        });
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
}
