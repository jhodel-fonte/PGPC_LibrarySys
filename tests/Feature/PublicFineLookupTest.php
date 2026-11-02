<?php

namespace Tests\Feature;

use App\Livewire\Pages\Dashboard\ProcessFinePayment;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PublicFineLookupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('students', function (Blueprint $t) { $t->id(); $t->string('school_id_number'); $t->softDeletes(); });
        Schema::create('book_datas', function (Blueprint $t) { $t->id(); $t->string('book_title'); $t->softDeletes(); });
        Schema::create('book_details', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('book_data_id'); $t->softDeletes(); });
        Schema::create('books', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('book_detail_id'); $t->string('accession_number'); $t->softDeletes(); });
        Schema::create('borrowing_transactions', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('school_id'); $t->unsignedBigInteger('book_id'); $t->dateTime('due_date'); $t->dateTime('return_date')->nullable(); });
        Schema::create('fine_types', function (Blueprint $t) { $t->id(); $t->string('name'); });
        Schema::create('fines', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('student_id'); $t->unsignedBigInteger('borrowing_transaction_id'); $t->unsignedBigInteger('fine_type_id'); $t->decimal('amount', 8, 2); $t->string('fine_status'); $t->date('fine_due_date')->nullable(); $t->dateTime('paid_at')->nullable(); });

        DB::table('students')->insert([['id' => 1, 'school_id_number' => '2026-001'], ['id' => 2, 'school_id_number' => '2026-002']]);
        DB::table('book_datas')->insert(['id' => 1, 'book_title' => 'Example book']);
        DB::table('book_details')->insert(['id' => 1, 'book_data_id' => 1]);
        DB::table('books')->insert(['id' => 1, 'book_detail_id' => 1, 'accession_number' => 'A-001']);
        DB::table('borrowing_transactions')->insert([
            ['id' => 1, 'school_id' => 1, 'book_id' => 1, 'due_date' => '2026-01-01', 'return_date' => '2026-01-05'],
            ['id' => 2, 'school_id' => 1, 'book_id' => 1, 'due_date' => '2026-01-01', 'return_date' => null],
            ['id' => 3, 'school_id' => 2, 'book_id' => 1, 'due_date' => '2026-01-01', 'return_date' => null],
        ]);
        DB::table('fine_types')->insert(['id' => 1, 'name' => 'Overdue']);
        DB::table('fines')->insert(['id' => 1, 'student_id' => 1, 'borrowing_transaction_id' => 1, 'fine_type_id' => 1, 'amount' => 200, 'fine_status' => 'unpaid']);
    }

    public function test_public_lookup_returns_current_fine_and_handles_missing_records(): void
    {
        $this->getJson('/api/v1/fines/student/2026-001')->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.book_title', 'Example book')->assertJsonPath('data.0.status', 'unpaid');
        $this->getJson('/api/v1/fines/student/2026-002')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/fines/student/missing')->assertNotFound();
        $this->getJson('/api/v1/fines/999')->assertNotFound();
        DB::table('fines')->where('id', 1)->update(['fine_status' => 'paid', 'amount' => 150]);
        $this->getJson('/api/v1/fines/1')->assertOk()->assertJsonPath('data.student_id', '2026-001')
            ->assertJsonPath('data.accession_number', 'A-001')->assertJsonPath('data.status', 'paid')->assertJsonPath('data.amount', '150.00');
    }

    public function test_payment_page_lists_only_selected_students_unreturned_loans(): void
    {
        $admin = \Mockery::mock(\App\Models\Account::class)->makePartial();
        $admin->shouldReceive('hasRole')->with('Admin')->andReturn(true);
        \Illuminate\Support\Facades\Auth::shouldReceive('user')->andReturn($admin);
        $page = new ProcessFinePayment();
        $page->studentId = 1;
        $data = $page->render()->getData();
        $this->assertSame([2], $data['activeLoans']->modelKeys());
        $this->assertSame([1], $data['fines']->modelKeys());
    }
}
