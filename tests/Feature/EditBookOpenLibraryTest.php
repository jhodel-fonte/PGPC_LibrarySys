<?php

namespace Tests\Feature;

use App\Livewire\Forms\EditBookForm;
use App\Models\Account;
use App\Models\Role;
use App\Livewire\Pages\Dashboard\BookDetail as BookDetailPage;
use App\Models\BookData;
use App\Models\BookDetail;
use App\Models\BookType;
use App\Models\Language;
use App\Models\Publisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class EditBookOpenLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_book_can_fill_metadata_from_isbn(): void
    {
        $data = BookData::create(['book_title' => 'Old title', 'language_id' => Language::query()->value('id')]);
        $detail = BookDetail::create(['book_data_id' => $data->id, 'catalog_kind' => 'book', 'call_number' => 'TEST', 'book_type_id' => BookType::query()->value('id'), 'publisher_id' => Publisher::query()->value('id')]);
        Http::fake([
            'openlibrary.org/api/books*' => Http::response(['ISBN:9780140328721' => ['title' => 'New title', 'number_of_pages' => 123, 'description' => 'A description']]),
            'openlibrary.org/isbn/*' => Http::response([], 404),
            'covers.openlibrary.org/*' => Http::response('', 404),
        ]);

        Livewire::test(EditBookForm::class, ['id' => $detail->id])
            ->assertSee('Auto-Fill')
            ->set('isbn', '978-0-14-032872-1')
            ->call('lookupIsbn')
            ->assertSet('bookTitle', 'New title')
            ->assertSet('pages', 123)
            ->assertSet('bookDescription', 'A description')
            ->assertSet('isbn', '9780140328721');
        $this->assertSame('Old title', $data->fresh()->book_title);
    }
    public function test_details_lookup_redirects_with_the_matching_data_and_reports_no_match(): void
    {
        $admin = Account::factory()->create(['role_id' => Role::where('name', 'Admin')->value('id')]);
        $data = BookData::create(['book_title' => 'Old title', 'language_id' => Language::query()->value('id')]);
        $detail = BookDetail::create(['book_data_id' => $data->id, 'catalog_kind' => 'book', 'isbn' => '9780140328721', 'call_number' => 'TEST', 'book_type_id' => BookType::query()->value('id'), 'publisher_id' => Publisher::query()->value('id')]);
        Http::fake([
            'openlibrary.org/api/books*' => Http::response(['ISBN:9780140328721' => ['title' => 'New title', 'number_of_pages' => 123, 'description' => 'A description']]),
            'openlibrary.org/isbn/*' => Http::response([], 404),
            'covers.openlibrary.org/*' => Http::response('', 404),
        ]);

        Livewire::actingAs($admin)->test(BookDetailPage::class)
            ->call('loadBookDetail', $detail->id)
            ->assertSee('Auto-Fill')
            ->call('autoFill')
            ->assertDispatched('open-library-match-found', url: route('admin.book-management.edit', $detail->id));
        Livewire::actingAs($admin)->test(EditBookForm::class, ['id' => $detail->id])
            ->assertSet('bookTitle', 'New title')
            ->assertSet('pages', 123);
        $this->assertSame('Old title', $data->fresh()->book_title);

        Http::fake([
            'openlibrary.org/api/books*' => Http::response([]),
            'openlibrary.org/isbn/*' => Http::response([], 404),
        ]);
        Livewire::actingAs($admin)->test(BookDetailPage::class)
            ->call('loadBookDetail', $detail->id)
            ->call('autoFill')
            ->assertSet('autofillError', 'No Open Library match was found for this ISBN.')
            ->assertNoRedirect();
    }

    public function test_invalid_book_edit_shows_error_modal(): void
    {
        $admin = Account::factory()->create(['role_id' => Role::where('name', 'Admin')->value('id')]);
        $data = BookData::create(['book_title' => 'Old title', 'language_id' => Language::query()->value('id')]);
        $detail = BookDetail::create(['book_data_id' => $data->id, 'catalog_kind' => 'book', 'call_number' => 'TEST', 'book_type_id' => BookType::query()->value('id'), 'publisher_id' => Publisher::query()->value('id')]);

        Livewire::actingAs($admin)->test(EditBookForm::class, ['id' => $detail->id])
            ->set('bookTitle', '')
            ->call('save')
            ->assertHasErrors(['bookTitle' => 'required'])
            ->assertDispatched('open-modal');
    }

}
