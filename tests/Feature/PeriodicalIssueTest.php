<?php

namespace Tests\Feature;

use App\Models\BookData;
use App\Models\BookDetail;
use App\Models\BookType;
use App\Models\Language;
use App\Models\Publisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodicalIssueTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_periodical_issues_share_a_title_and_keep_separate_policies(): void
    {
        $title = BookData::create(['book_title' => 'Library Journal', 'language_id' => Language::query()->value('id')]);
        $attributes = ['book_data_id' => $title->id, 'catalog_kind' => 'journal', 'format' => 'Journal', 'call_number' => 'TEST', 'book_type_id' => BookType::query()->value('id'), 'publisher_id' => Publisher::query()->value('id')];
        $first = BookDetail::create($attributes);
        $second = BookDetail::create($attributes);
        $first->periodicalIssue()->create(['volume' => '1', 'issue_number' => '1', 'borrowable' => false]);
        $second->periodicalIssue()->create(['volume' => '1', 'issue_number' => '2', 'borrowable' => true, 'loan_days' => 7]);

        $this->assertCount(2, $title->bookDetails);
        $this->assertFalse($first->periodicalIssue->borrowable);
        $this->assertSame(7, $second->periodicalIssue->loan_days);
        $this->assertSame(2, BookDetail::publicCatalog()->where('book_data_id', $title->id)->count());
    }
    public function test_deleting_one_issue_preserves_other_issues_and_records(): void
    {
        $title = BookData::create(['book_title' => 'Test Journal', 'language_id' => Language::query()->value('id')]);
        $attributes = ['book_data_id' => $title->id, 'catalog_kind' => 'journal', 'format' => 'Journal', 'call_number' => 'TEST', 'book_type_id' => BookType::query()->value('id'), 'publisher_id' => Publisher::query()->value('id')];
        $first = BookDetail::create($attributes);
        $second = BookDetail::create($attributes);
        $first->periodicalIssue()->create(['issue_number' => '1']);
        $second->periodicalIssue()->create(['issue_number' => '2']);
        $condition = \App\Models\BookCondition::query()->value('id');
        $copy1 = \App\Models\Book::create(['book_detail_id' => $first->id, 'book_condition_id' => $condition, 'accession_number' => 'ISSUE-1', 'status' => 'available']);
        $copy2 = \App\Models\Book::create(['book_detail_id' => $second->id, 'book_condition_id' => $condition, 'accession_number' => 'ISSUE-2', 'status' => 'available']);

        app(\App\Services\CatalogDeletionService::class)->delete('issue', [$first->id], false);
        $this->assertTrue($first->fresh()->trashed());
        $this->assertTrue($copy1->fresh()->trashed());
        $this->assertNotNull(BookDetail::find($second->id));
        $this->assertNotNull(\App\Models\Book::find($copy2->id));
        $this->assertNotNull($title->fresh());
        $this->assertDatabaseHas('periodical_issues', ['book_detail_id' => $first->id]);
    }

    public function test_marc_import_creates_issues_under_one_title(): void
    {
        $service = app(\App\Services\ExtractMarcData::class);
        $result = $service->importRecords([
            ['title' => 'MARC Journal', 'catalog_kind' => 'journal', 'issn' => '1234-5678', 'issue_label' => 'Issue 1'],
            ['title' => 'MARC Journal', 'catalog_kind' => 'journal', 'issn' => '1234-5678', 'issue_label' => 'Issue 2'],
        ], ['accession_prefix' => 'MARCTEST-', 'start_accession_number' => 1]);
        $this->assertSame(2, $result['imported_count']);
        $title = BookData::where('book_title', 'MARC Journal')->firstOrFail();
        $this->assertSame(2, $title->bookDetails()->where('catalog_kind', 'journal')->count());
        $this->assertDatabaseHas('periodical_issues', ['issue_label' => 'Issue 1']);
        $this->assertDatabaseHas('periodical_issues', ['issue_label' => 'Issue 2']);
    }
    public function test_add_form_shows_fields_for_the_selected_catalog_type(): void
    {
        $form = \Livewire\Livewire::test(\App\Livewire\Forms\AddBookForm::class);
        $form->assertSee('Book Title')->assertSee('Auto-Fill');
        $form->set('catalogKind', 'journal')->assertSee('Journal Title')->assertSee('Issue number')
            ->assertSee('Publisher')->assertSee('Save Journal')->assertDontSee('Asset metadata')->assertDontSee('Auto-Fill');
        $form->set('catalogKind', 'magazine')->assertSee('Magazine Title')->assertSee('Save Magazine');
        $form->set('catalogKind', 'equipment')->assertSee('Item Name')->assertSee('Asset metadata')
            ->assertSee('Save Physical Item')->assertDontSee('Issue number');
    }
    public function test_catalog_form_saves_each_item_type_and_issue_details(): void
    {
        foreach (['book', 'equipment', 'magazine', 'journal'] as $kind) {
            $form = \Livewire\Livewire::test(\App\Livewire\Forms\AddBookForm::class)
                ->set('catalogKind', $kind)
                ->set('bookTitle', 'Test '.$kind)
                ->set('accessionNumber', 'TEST-'.$kind)
                ->set('callNumber', 'TEST')
                ->set('newAuthorFirstName', 'Test')
                ->set('newAuthorLastName', 'Author');
            if (in_array($kind, ['magazine', 'journal'], true)) {
                $form->set('issueNumber', '12')->set('issueBorrowable', false)->set('issueLoanDays', 7);
            }
            $form->call('save')->assertHasNoErrors()->assertRedirect();
            $detail = BookDetail::where('catalog_kind', $kind)->whereHas('bookData', fn ($q) => $q->where('book_title', 'Test '.$kind))->firstOrFail();
            $this->assertDatabaseHas('books', ['book_detail_id' => $detail->id, 'accession_number' => 'TEST-'.$kind]);
            if (in_array($kind, ['magazine', 'journal'], true)) {
                $this->assertDatabaseHas('periodical_issues', ['book_detail_id' => $detail->id, 'issue_number' => '12', 'borrowable' => false, 'loan_days' => 7]);
            }
        }
    }

    public function test_book_cannot_be_saved_without_an_author(): void
    {
        \Livewire\Livewire::test(\App\Livewire\Forms\AddBookForm::class)
            ->set('bookTitle', 'Missing author')->set('accessionNumber', 'NO-AUTHOR')->set('callNumber', 'TEST')
            ->call('save')->assertHasErrors(['authorSearch']);
        $this->assertDatabaseMissing('books', ['accession_number' => 'NO-AUTHOR']);
    }
    public function test_catalog_filters_separate_issues_and_physical_items(): void
    {
        foreach (['book', 'equipment', 'magazine', 'journal'] as $kind) {
            $title = BookData::create(['book_title' => 'Filter '.$kind, 'language_id' => Language::query()->value('id')]);
            $detail = BookDetail::create(['book_data_id' => $title->id, 'catalog_kind' => $kind, 'format' => 'Book', 'call_number' => 'TEST', 'book_type_id' => BookType::query()->value('id'), 'publisher_id' => Publisher::query()->value('id')]);
            \App\Models\Book::create(['book_detail_id' => $detail->id, 'book_condition_id' => \App\Models\BookCondition::query()->value('id'), 'accession_number' => 'FILTER-'.$kind, 'status' => 'available']);
        }
        $manager = new class extends \App\Livewire\Pages\Dashboard\BookManager {
            public function accessions(): array { return $this->buildQuery()->where('books.accession_number', 'like', 'FILTER-%')->pluck('books.accession_number')->sort()->values()->all(); }
        };
        $manager->inventoryView = 'Copies';
        $this->assertCount(4, $manager->accessions());
        foreach (['books' => 'book', 'physical' => 'equipment', 'magazine' => 'magazine', 'journal' => 'journal'] as $filter => $kind) {
            $manager->catalogFilter = $filter;
            $this->assertSame(['FILTER-'.$kind], $manager->accessions());
        }
    }
    public function test_catalog_detail_modal_displays_fields_for_each_type(): void
    {
        $title = BookData::create(['book_title' => 'Example item', 'language_id' => Language::query()->value('id')]);
        $attributes = ['book_data_id' => $title->id, 'format' => 'Book', 'call_number' => 'TEST', 'book_type_id' => BookType::query()->value('id'), 'publisher_id' => Publisher::query()->value('id')];
        $journal = BookDetail::create($attributes + ['catalog_kind' => 'journal', 'issn' => '1234-5678']);
        $journal->periodicalIssue()->create(['volume' => '8', 'issue_number' => '2', 'borrowable' => false]);
        \Livewire\Livewire::test(\App\Livewire\Pages\Dashboard\BookDetail::class)->call('loadBookDetail', $journal->id)
            ->assertSee('Journal')->assertSee('1234-5678')->assertSee('Issue Number')->assertSee('Library use only')->assertDontSee('Manufacturer / Brand');
        $item = BookDetail::create($attributes + ['catalog_kind' => 'equipment', 'manufacturer' => 'Example Maker']);
        \Livewire\Livewire::test(\App\Livewire\Pages\Dashboard\BookDetail::class)->call('loadBookDetail', $item->id)
            ->assertSee('Physical Item')->assertSee('Example Maker')->assertSee('Package Contents')->assertDontSee('Issue Number');
    }
}
