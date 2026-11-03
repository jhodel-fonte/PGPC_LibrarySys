<?php

namespace Tests\Feature;

use App\Livewire\Pages\Dashboard\BorrowedList;
use App\Models\Account;
use App\Models\Role;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class BookBorrowingHistoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        foreach (['borrowing_transactions', 'books', 'book_details', 'book_datas', 'students'] as $table) Schema::dropIfExists($table);
        Schema::create('book_datas', function (Blueprint $t) { $t->id(); $t->string('book_title'); $t->softDeletes(); });
        Schema::create('book_details', function (Blueprint $t) { $t->id(); $t->integer('book_data_id'); $t->softDeletes(); });
        Schema::create('books', function (Blueprint $t) { $t->id(); $t->integer('book_detail_id'); $t->string('accession_number'); $t->softDeletes(); });
        Schema::create('students', function (Blueprint $t) { $t->id(); $t->integer('account_id')->nullable(); $t->string('first_name'); $t->string('last_name'); $t->string('program')->nullable(); $t->string('year_level')->nullable(); $t->softDeletes(); });
        Schema::create('borrowing_transactions', function (Blueprint $t) { $t->id(); $t->integer('school_id'); $t->integer('book_id'); $t->integer('issued_by_id')->nullable(); $t->integer('received_by_id')->nullable(); $t->dateTime('issued_date'); $t->dateTime('due_date'); $t->dateTime('return_date')->nullable(); $t->dateTime('lost_at')->nullable(); });
        $admin = new Account;
        $admin->setRelation('role', new Role(['name' => 'Admin']));
        Auth::setUser($admin);
    }

    public function test_book_filter_includes_deleted_copies_and_students_and_scopes_counts(): void
    {
        DB::table('book_datas')->insert([['id' => 1, 'book_title' => 'Target'], ['id' => 2, 'book_title' => 'Other']]);
        DB::table('book_details')->insert([['id' => 1, 'book_data_id' => 1], ['id' => 2, 'book_data_id' => 2]]);
        DB::table('books')->insert([
            ['id' => 1, 'book_detail_id' => 1, 'accession_number' => 'A', 'deleted_at' => null],
            ['id' => 2, 'book_detail_id' => 1, 'accession_number' => 'B', 'deleted_at' => now()],
            ['id' => 3, 'book_detail_id' => 2, 'accession_number' => 'C', 'deleted_at' => null],
        ]);
        DB::table('students')->insert(['id' => 1, 'first_name' => 'Former', 'last_name' => 'Student', 'deleted_at' => now()]);
        foreach ([1, 2, 3] as $id) {
            DB::table('borrowing_transactions')->insert(['school_id' => 1, 'book_id' => $id, 'issued_date' => now()->subDays(5 + $id), 'due_date' => now()->subDays(2), 'return_date' => $id === 2 ? now()->subDay() : null]);
        }
        $component = new BorrowedList;
        $component->bookDetailId = '1';
        $data = $component->render()->getData();
        $this->assertSame(2, $data['totalHistoryCount']);
        $this->assertSame(1, $data['activeCount']);
        $this->assertSame(1, $data['overdueCount']);
        $this->assertSame(1, $data['returnedCount']);
        $this->assertSame([1, 2], $data['borrowings']->pluck('book_id')->all());
        $this->assertTrue($data['borrowings']->last()->book->trashed());
        $this->assertTrue($data['borrowings']->first()->student->trashed());
    }

    public function test_access_requires_circulation_permission(): void
    {
        Auth::logout();
        $this->expectException(HttpException::class);
        (new BorrowedList)->render();
    }
}
