<?php

namespace Tests\Feature;

use App\Jobs\ProcessCatalogImportJob;
use App\Livewire\Components\BookManager\CatalogSpreadsheetImport;
use App\Models\Account;
use App\Models\CatalogImport;
use App\Services\CatalogSpreadsheetImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class CatalogImportProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_livewire_upload_endpoint_stores_a_csv_temporarily(): void
    {
        Storage::fake('tmp-for-tests');
        $url = URL::temporarySignedRoute('livewire.upload-file', now()->addMinutes(5), [], false);
        $file = UploadedFile::fake()->createWithContent('catalog.csv', "title\nExample\n");

        $this->postJson($url, ['files' => [$file]])
            ->assertOk()
            ->assertJsonStructure(['paths']);

        $this->assertNotEmpty(Storage::disk('tmp-for-tests')->files('livewire-tmp'));
    }

    public function test_uploaded_csv_can_be_reviewed_and_queued(): void
    {
        Queue::fake();
        $user = Account::factory()->create(['role_id' => 1]);
        $file = UploadedFile::fake()->createWithContent('catalog.csv', "title,author\nTest Book,Test Author\n");

        Livewire::actingAs($user)->test(CatalogSpreadsheetImport::class)
            ->set('spreadsheet', $file)
            ->assertSet('hasSelectedFile', true)
            ->assertSee('Review Records')
            ->call('review')
            ->assertSee('Test Book')
            ->call('importSpreadsheet')
            ->assertSee('Importing Records');

        Queue::assertPushed(ProcessCatalogImportJob::class);
    }

    public function test_reviewed_csv_is_actually_imported_by_the_job(): void
    {
        Notification::fake();
        $user = Account::factory()->create(['role_id' => 1]);
        $file = UploadedFile::fake()->createWithContent('catalog.csv', "title,author,accession_no\nImport Test Book,Test Author,IMPORT-999\n");
        $component = Livewire::actingAs($user)->test(CatalogSpreadsheetImport::class)
            ->set('spreadsheet', $file)->call('review');
        $import = CatalogImport::findOrFail($component->get('catalogImportId'));
        $import->update(['status' => 'importing']);

        (new ProcessCatalogImportJob($import->id, $user->id, route('admin.book-management.index')))
            ->handle(app(CatalogSpreadsheetImportService::class));

        $this->assertSame('completed', $import->fresh()->status);
        $this->assertDatabaseHas('books', ['accession_number' => 'IMPORT-999']);
        $this->assertDatabaseHas('catalog_import_rows', ['catalog_import_id' => $import->id, 'status' => 'imported']);
    }

    public function test_import_dispatches_with_correct_route_and_shows_progress(): void
    {
        Queue::fake();
        $user = Account::factory()->create(['role_id' => 1]);
        $import = CatalogImport::create(['uploaded_by' => $user->id, 'source_file_name' => 'test.csv', 'source_type' => 'csv', 'status' => 'reviewing', 'total_rows' => 2]);
        $import->rows()->create(['row_number' => 2, 'raw_data' => ['title' => 'One'], 'status' => 'pending']);
        $import->rows()->create(['row_number' => 3, 'raw_data' => ['title' => ''], 'status' => 'invalid', 'errors' => ['Title is required.']]);

        Livewire::actingAs($user)->test(CatalogSpreadsheetImport::class)
            ->set('catalogImportId', $import->id)
            ->call('importSpreadsheet')
            ->assertSee('50%')
            ->assertSee('Title is required.');

        $this->assertSame('importing', $import->fresh()->status);
        Queue::assertPushed(ProcessCatalogImportJob::class, fn ($job) => $job->catalogImportId === $import->id && $job->actionUrl === route('admin.book-management.index') && $job->connection === 'database');
    }

    public function test_cancelled_batch_keeps_imported_rows_and_queued_job_does_not_import_remaining_rows(): void
    {
        Queue::fake();
        $user = Account::factory()->create(['role_id' => 1]);
        $import = CatalogImport::create(['uploaded_by' => $user->id, 'source_file_name' => 'test.csv', 'source_type' => 'csv', 'status' => 'importing', 'total_rows' => 2, 'imported_rows' => 1]);
        $import->rows()->create(['row_number' => 2, 'raw_data' => ['title' => 'Already imported'], 'status' => 'imported']);
        $import->rows()->create(['row_number' => 3, 'raw_data' => ['title' => 'Waiting'], 'status' => 'pending']);

        Livewire::actingAs($user)->test(CatalogSpreadsheetImport::class)
            ->set('catalogImportId', $import->id)
            ->call('cancelImport')
            ->assertSee('Import cancelled');

        (new ProcessCatalogImportJob($import->id, $user->id))->handle(app(CatalogSpreadsheetImportService::class));
        $this->assertSame('cancelled', $import->fresh()->status);
        $this->assertSame(1, $import->rows()->where('status', 'imported')->count());
        $this->assertSame(1, $import->rows()->where('status', 'pending')->count());
    }

    public function test_failure_is_recorded_for_the_page_and_completed_import_is_not_overwritten(): void
    {
        $user = Account::factory()->create(['role_id' => 1]);
        $import = CatalogImport::create(['uploaded_by' => $user->id, 'source_file_name' => 'test.csv', 'source_type' => 'csv', 'status' => 'importing', 'total_rows' => 1]);
        $job = new ProcessCatalogImportJob($import->id, $user->id);
        $job->failed(new \RuntimeException('Worker stopped'));

        $this->assertSame('failed', $import->fresh()->status);
        Livewire::actingAs($user)->test(CatalogSpreadsheetImport::class)
            ->set('catalogImportId', $import->id)
            ->assertSee('The import stopped before all rows were processed');

        $import->update(['status' => 'completed']);
        $job->failed(new \RuntimeException('Late failure'));
        $this->assertSame('completed', $import->fresh()->status);
    }
}
