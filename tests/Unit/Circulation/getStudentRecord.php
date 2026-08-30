<?php

namespace Tests\Unit\Circulation;

use Tests\TestCase;
use App\Models\Student;
use App\Models\LibraryStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class getStudentRecord extends TestCase
{
    use RefreshDatabase;

    public function test_can_retrieve_student_record_by_school_id_number(): void
    {
        LibraryStatus::create(['id' => 1, 'status' => 'Active']);

        $student = Student::create([
            'school_id_number' => '2026-9999',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'library_status_id' => 1,
            'program' => 'BSIS',
            'year_level' => '3'
        ]);

        $retrieved = Student::where('school_id_number', '2026-9999')->first();

        $this->assertNotNull($retrieved);
        $this->assertEquals('Jane', $retrieved->first_name);
        $this->assertEquals('Smith', $retrieved->last_name);
    }
}

