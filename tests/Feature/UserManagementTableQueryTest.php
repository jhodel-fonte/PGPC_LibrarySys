<?php

namespace Tests\Feature;

use App\Livewire\Components\Usermanager\LibrarianTable;
use App\Livewire\Components\Usermanager\StudentTable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserManagementTableQueryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('roles', function (Blueprint $t) { $t->id(); $t->string('name'); });
        Schema::create('account_statuses', function (Blueprint $t) { $t->id(); $t->string('status_name'); });
        Schema::create('accounts', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('role_id'); $t->unsignedBigInteger('status_id')->nullable(); $t->string('username')->nullable(); $t->string('email')->nullable(); $t->timestamp('last_login')->nullable(); $t->softDeletes(); });
        Schema::create('students', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('account_id'); $t->string('first_name'); $t->string('last_name'); $t->string('school_id_number'); $t->string('program')->nullable(); $t->string('year_level')->nullable(); $t->softDeletes(); });
        Schema::create('librarians', function (Blueprint $t) { $t->id(); $t->unsignedBigInteger('account_id'); $t->string('first_name'); $t->string('last_name'); $t->string('school_id_number'); $t->softDeletes(); });
        DB::table('roles')->insert([['id' => 1, 'name' => 'Student'], ['id' => 2, 'name' => 'Librarian'], ['id' => 3, 'name' => 'Head Librarian']]);
        DB::table('account_statuses')->insert([['id' => 1, 'status_name' => 'Active'], ['id' => 2, 'status_name' => 'Locked']]);
        DB::table('accounts')->insert([
            ['id' => 1, 'role_id' => 1, 'status_id' => 1, 'deleted_at' => null],
            ['id' => 2, 'role_id' => 1, 'status_id' => 2, 'deleted_at' => null],
            ['id' => 3, 'role_id' => 1, 'status_id' => 1, 'deleted_at' => '2026-01-01'],
            ['id' => 4, 'role_id' => 2, 'status_id' => 1, 'deleted_at' => null],
            ['id' => 5, 'role_id' => 3, 'status_id' => 1, 'deleted_at' => null],
        ]);
        DB::table('students')->insert([
            ['id' => 1, 'account_id' => 1, 'first_name' => 'Anna', 'last_name' => 'A', 'school_id_number' => 'S1'],
            ['id' => 2, 'account_id' => 2, 'first_name' => 'Ben', 'last_name' => 'B', 'school_id_number' => 'S2'],
            ['id' => 3, 'account_id' => 3, 'first_name' => 'Cora', 'last_name' => 'C', 'school_id_number' => 'S3'],
        ]);
        DB::table('librarians')->insert([
            ['id' => 1, 'account_id' => 4, 'first_name' => 'Dan', 'last_name' => 'D', 'school_id_number' => 'L1'],
            ['id' => 2, 'account_id' => 5, 'first_name' => 'Eve', 'last_name' => 'E', 'school_id_number' => 'L2'],
        ]);
        $admin = \Mockery::mock(\App\Models\Account::class)->makePartial();
        $admin->shouldReceive('hasRole')->with('Admin')->andReturn(true);
        \Illuminate\Support\Facades\Auth::shouldReceive('user')->andReturn($admin);
    }

    public function test_counts_filters_and_validated_sorting(): void
    {
        $students = new StudentTable();
        $students->sort = ['column' => 'invalid', 'direction' => 'invalid'];
        DB::enableQueryLog();
        $studentData = $students->render()->getData();
        $studentQueries = DB::getQueryLog();
        DB::flushQueryLog();
        $staff = new LibrarianTable();
        $staffData = $staff->render()->getData();
        $staffQueries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertSame([2, 1, 0, 1, 1], array_map(fn ($key) => $studentData[$key], ['totalStudents', 'activeStudents', 'inactiveStudents', 'lockedStudents', 'deletedStudents']));
        $this->assertSame([2, 1, 1, 0], array_map(fn ($key) => $staffData[$key], ['totalLibrarians', 'headLibrarians', 'regularLibrarians', 'deletedLibrarians']));
        $this->assertSame(1, count(array_filter($studentQueries, fn ($q) => str_contains($q['query'], 'sum(case'))));
        $this->assertSame(1, count(array_filter($staffQueries, fn ($q) => str_contains($q['query'], 'sum(case'))));
        $students->activeTab = 'Suspended';
        $this->assertSame([2], $students->render()->getData()['students']->modelKeys());
        $students->activeTab = 'Deleted';
        $this->assertSame([3], $students->render()->getData()['students']->modelKeys());
        $students->activeTab = 'ALL';
        $students->search = 'Anna';
        $this->assertSame([1], $students->render()->getData()['students']->modelKeys());
        $students->sortBy('not_a_column');
        $this->assertSame('invalid', $students->sort['column']);
        $staff->activeTab = 'Head Librarian';
        $this->assertSame([5], $staff->render()->getData()['librarians']->modelKeys());
    }
}
