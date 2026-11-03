<?php

namespace Tests\Feature;

use App\Livewire\Pages\Main\Book as BookPage;
use App\Models\Author;
use App\Models\Book;
use App\Models\BookCondition;
use App\Models\BookData;
use App\Models\BookDetail;
use App\Models\BookType;
use App\Models\Language;
use App\Models\Publisher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OpacAuthorWorksTest extends TestCase
{
    use RefreshDatabase;

    public function test_other_works_only_include_public_titles_with_an_active_copy_and_a_shared_author(): void
    {
        $author = Author::create(['first_name' => 'Jane', 'last_name' => 'Writer']);
        $makeWork = function (string $title, bool $shared, string $status = 'available') use ($author) {
            $data = BookData::create(['book_title' => $title, 'language_id' => Language::query()->value('id')]);
            if ($shared) {
                $data->authors()->attach($author);
            }
            $detail = BookDetail::create(['book_data_id' => $data->id, 'catalog_kind' => 'book', 'format' => 'Book', 'call_number' => 'TEST', 'book_type_id' => BookType::query()->value('id'), 'publisher_id' => Publisher::query()->value('id')]);
            Book::create(['book_detail_id' => $detail->id, 'book_condition_id' => BookCondition::query()->value('id'), 'accession_number' => $title, 'status' => $status]);
            return $detail;
        };

        $current = $makeWork('Current title', true);
        $other = $makeWork('Related title', true);
        $unrelated = $makeWork('Unrelated title', false);
        $unrelated->books()->first()->update(['accession_number' => (string) $other->id]);
        $makeWork('Archived title', true, 'archived');

        Livewire::test(BookPage::class, ['identifier' => $current->id])
            ->call('setTab', 'authors')
            ->assertSee('Related title')
            ->assertSee(route('opac.book.detail', 'Related title'))
            ->assertDontSee('Unrelated title')
            ->assertDontSee('Archived title');

        Livewire::test(BookPage::class, ['identifier' => 'Related title'])
            ->assertSet('bookDetailId', $other->id);
    }
}
