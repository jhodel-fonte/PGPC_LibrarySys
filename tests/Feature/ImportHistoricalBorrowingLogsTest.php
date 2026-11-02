<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ImportHistoricalBorrowingLogsTest extends TestCase
{
    public function test_import_preserves_unknown_fields_and_does_not_duplicate_rows(): void
    {
        (require database_path('migrations/2026_11_03_000001_create_historical_borrowing_logs_table.php'))->up();
        $path = tempnam(sys_get_temp_dir(), 'history');
        file_put_contents($path, "date,accession_number,source_image,page,review_note\n2026-07-15,05549,image.jpg,60,verify\n,,image.jpg,61,uncertain\n");

        try {
            $this->artisan('history:import-borrowings', ['csv' => $path])->assertSuccessful();
            $this->artisan('history:import-borrowings', ['csv' => $path])->assertSuccessful();
            $this->assertDatabaseCount('historical_borrowing_logs', 2);
            $this->assertDatabaseHas('historical_borrowing_logs', ['borrowed_date' => '2026-07-15', 'accession_number' => '05549', 'review_note' => 'verify']);
            $this->assertDatabaseHas('historical_borrowing_logs', ['borrowed_date' => null, 'accession_number' => null, 'page' => '61']);
        } finally {
            unlink($path);
            Schema::dropIfExists('historical_borrowing_logs');
        }
    }
}
