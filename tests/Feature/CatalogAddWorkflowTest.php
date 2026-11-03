<?php

namespace Tests\Feature;

use App\Livewire\Forms\AddBookForm;
use App\Livewire\Forms\EditBookForm;
use App\Models\BookCondition;
use App\Models\BookData;
use App\Models\BookDetail;
use App\Models\BookType;
use App\Models\Language;
use App\Models\Publisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CatalogAddWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = false;

    protected function setUp(): void
    {
        parent::setUp();
        BookType::create(['type' => 'Book']);
        BookCondition::create(['status' => 'Good']);
        Language::create(['lang' => 'English']);
        Publisher::create(['name' => 'Example Publisher']);
    }

    public function test_equipment_saves_without_stale_periodical_or_book_metadata(): void
    {
        Livewire::test(AddBookForm::class)->set('catalogKind', 'journal')->set('issueDate', 'invalid')
            ->set('catalogKind', 'equipment')->set('bookTitle', 'Calculator')->set('accessionNumber', 'CALC-1')
            ->set('issueDate', 'invalid')->set('isbn', 'irrelevant')->set('authorSearch', 'Unrelated author')
            ->set('manufacturer', 'Example')->call('submit')->assertHasNoErrors()->assertSet('errorMessage', '');
        $detail = BookDetail::where('catalog_kind', 'equipment')->firstOrFail();
        $this->assertSame('Example', $detail->manufacturer);
        $this->assertNull($detail->isbn);
        $this->assertCount(0, $detail->bookData->authors);
        $this->assertNull($detail->periodicalIssue);
    }

    public function test_isbn_dashes_do_not_count_toward_length_on_add_or_edit(): void
    {
        $isbn = '978-0-14-032872-1';
        Livewire::test(AddBookForm::class)->set('bookTitle', 'Example')->set('accessionNumber', 'ISBN-1')
            ->set('callNumber', 'QA1')->set('authorSearch', 'Example Author')->set('isbn', $isbn)
            ->call('submit')->assertHasNoErrors()->assertSet('errorMessage', '');
        $detail = BookDetail::where('isbn', $isbn)->firstOrFail();

        $form = Livewire::test(EditBookForm::class, ['id' => $detail->id]);
        $form->set('isbn', '978-1-23-456789-0')->call('save')->assertHasNoErrors()->assertSet('errorMessage', '');
        $this->assertSame('978-1-23-456789-0', $detail->fresh()->isbn);
        $form->set('isbn', '1234-5678-9012-3456')->call('save')->assertHasErrors(['isbn']);
    }

    public function test_duplicate_issue_preserves_existing_record(): void
    {
        $title = BookData::create(['book_title' => 'Journal', 'language_id' => Language::query()->value('id')]);
        $detail = BookDetail::create(['book_data_id' => $title->id, 'catalog_kind' => 'journal', 'format' => 'Journal', 'call_number' => '', 'book_type_id' => BookType::query()->value('id'), 'publisher_id' => Publisher::query()->value('id')]);
        $detail->periodicalIssue()->create(['issue_number' => '2']);
        Livewire::test(AddBookForm::class)->set('catalogKind', 'journal')->set('periodicalTitleId', $title->id)
            ->set('accessionNumber', 'ISSUE-2')->set('issueNumber', '2')->call('submit')
            ->assertHasErrors(['issueNumber'])->assertSee('Add another copy');
        $this->assertDatabaseMissing('books', ['accession_number' => 'ISSUE-2']);
    }

    public function test_all_four_record_types_can_be_created(): void
    {
        foreach (['book', 'magazine', 'journal', 'equipment'] as $kind) {
            $form = Livewire::test(AddBookForm::class)->set('catalogKind', $kind)
                ->set('bookTitle', 'New '.$kind)->set('accessionNumber', 'NEW-'.$kind);
            if ($kind === 'book') {
                $form->set('callNumber', 'QA1')->set('authorSearch', 'Example Author');
            } elseif (in_array($kind, ['magazine', 'journal'], true)) {
                $form->set('issueLabel', 'Spring');
            }
            $form->call('submit')->assertHasNoErrors()->assertSet('errorMessage', '');
            $this->assertDatabaseHas('book_details', ['catalog_kind' => $kind]);
            $this->assertDatabaseHas('books', ['accession_number' => 'NEW-'.$kind]);
        }
    }

    public function test_existing_title_is_reused_for_new_issue(): void
    {
        $title = BookData::create(['book_title' => 'Existing Journal', 'subtitle' => 'Original subtitle', 'language_id' => Language::query()->value('id')]);
        $detail = BookDetail::create(['book_data_id' => $title->id, 'catalog_kind' => 'journal', 'format' => 'Journal', 'call_number' => '', 'book_type_id' => BookType::query()->value('id'), 'publisher_id' => Publisher::query()->value('id')]);
        $detail->periodicalIssue()->create(['issue_number' => '1']);
        Livewire::test(AddBookForm::class)->set('catalogKind', 'journal')->set('periodicalTitleId', $title->id)
            ->set('accessionNumber', 'ISSUE-NEW')->set('issueNumber', '2')
            ->call('submit')->assertHasNoErrors()->assertSet('errorMessage', '');
        $this->assertSame('Original subtitle', $title->fresh()->subtitle);
        $this->assertSame(2, $title->bookDetails()->where('catalog_kind', 'journal')->count());
        $this->assertDatabaseHas('books', ['accession_number' => 'ISSUE-NEW']);
    }
}
