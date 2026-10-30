<?php

namespace Tests\Unit;

use App\Services\StudentImportColumns;
use PHPUnit\Framework\TestCase;

class StudentImportColumnsTest extends TestCase
{
    public function test_it_detects_columns_in_any_order_and_ignores_age(): void
    {
        $columns = new StudentImportColumns;
        $this->assertSame(['email', '', 'school_id_number', 'last_name', 'first_name'],
            $columns->detect(['EMAIL address', 'Age', 'Student ID', 'Surname', 'Given Name']));
        $this->assertSame(2, $columns->headerRow([['Student list'], [], ['Email', 'Student ID', 'First Name']]));
    }

    public function test_duplicate_headers_are_left_for_manual_mapping(): void
    {
        $this->assertSame(['', '', 'first_name'], (new StudentImportColumns)->detect(['Email', 'Email Address', 'First name']));
        $this->assertNull((new StudentImportColumns)->headerRow([['Names', 'Age']]));
    }
}
