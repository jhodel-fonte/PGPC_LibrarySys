<?php

namespace Tests\Feature;

use App\Livewire\Components\Main\PersonalRecommendations;
use App\Models\Account;
use App\Models\Book;
use App\Models\BookCondition;
use App\Models\BookData;
use App\Models\BookDetail;
use App\Models\BookType;
use App\Models\Language;
use App\Models\Publisher;
use App\Models\UserHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class PersonalRecommendationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_viewed_isbn_does_not_use_stale_recommendations_or_call_api(): void
    {
        $user = Account::factory()->create(['role_id' => 4]);
        $viewed = $this->catalogBook('1111111111', 'Viewed');
        $recommended = $this->catalogBook('1234567890', 'Old recommendation');
        \App\Models\UserRecommendation::storeRecommendations($user->id, [$recommended->isbn]);
        $viewed->update(['isbn' => null, 'issn' => '1234-5678']);
        Http::fake();

        Livewire::actingAs($user)->test(PersonalRecommendations::class, ['bookDetailId' => $viewed->id])
            ->call('loadRecommendations')->assertSet('books', [])->assertDontSee('Picked for you');
        Http::assertNothingSent();
    }

    public function test_only_exact_isbn_matches_are_shown_without_filler(): void
    {
        $user = Account::factory()->create(['role_id' => 4]);
        $viewed = $this->catalogBook('1111111111', 'Viewed');
        $match = $this->catalogBook('2222222222', 'Exact match');
        $this->catalogBook('2222222223', 'Similar ISBN');
        Http::fake(['*' => Http::response(['recommendations' => [['isbn' => $match->isbn], ['isbn' => '9999999999']]])]);

        $component = Livewire::actingAs($user)->test(PersonalRecommendations::class, ['bookDetailId' => $viewed->id]);
        $component->call('loadRecommendations')->assertSee('Exact match')->assertDontSee('Similar ISBN');
        $this->assertSame([$match->id], array_column($component->get('books'), 'id'));
    }

    public function test_empty_and_unmatched_recommendations_are_distinguished_in_console(): void
    {
        $user = Account::factory()->create(['role_id' => 4]);
        $book = $this->catalogBook('7777777777', 'Viewed');
        Http::fakeSequence()->push(['recommendations' => []])->push(['recommendations' => [['isbn' => '9999999999', 'predicted_score' => 1.7]]]);

        Livewire::actingAs($user)->test(PersonalRecommendations::class, ['bookDetailId' => $book->id])
            ->call('loadRecommendations')->assertSet('books', [])->assertDontSee('Picked for you')
            ->assertDispatched('ml-recommendation-response', fn ($name, $params) => $params['response']['result'] === 'empty_recommendations');
        Livewire::actingAs($user)->test(PersonalRecommendations::class, ['bookDetailId' => $book->id])
            ->call('loadRecommendations')->assertSet('books', [])->assertDontSee('Picked for you')
            ->assertDispatched('ml-recommendation-response', fn ($name, $params) => $params['response']['result'] === 'no_catalog_matches' && $params['response']['unmatched_isbns'] === ['9999999999']);
    }

    public function test_malformed_recommendations_do_not_hide_valid_books(): void
    {
        $user = Account::factory()->create(['role_id' => 4]);
        $book = $this->catalogBook('8888888888', 'Viewed');
        $recommended = $this->catalogBook('9999999999', 'Valid recommendation');
        Http::fake(['*' => Http::response(['recommendations' => [['bad' => 'data'], ['isbn' => $recommended->isbn, 'predicted_score' => 1.7]]])]);
        Livewire::actingAs($user)->test(PersonalRecommendations::class, ['bookDetailId' => $book->id])
            ->call('loadRecommendations')->assertSee('Valid recommendation')
            ->assertDispatched('ml-recommendation-response', fn ($name, $params) => $params['response']['invalid_count'] === 1 && $params['response']['matched_count'] === 1);
    }

    private function catalogBook(string $isbn, string $title): BookDetail
    {
        $data = BookData::create(['book_title' => $title, 'language_id' => Language::query()->value('id')]);
        $detail = BookDetail::create(['book_data_id' => $data->id, 'isbn' => $isbn, 'format' => 'Book', 'call_number' => 'TEST', 'catalog_kind' => 'book', 'book_type_id' => BookType::query()->value('id'), 'publisher_id' => Publisher::query()->value('id')]);
        Book::create(['book_detail_id' => $detail->id, 'book_condition_id' => BookCondition::query()->value('id'), 'accession_number' => 'ACC-'.$isbn, 'code' => 'BC-'.$isbn, 'status' => 'available']);
        return $detail;
    }

    public function test_book_view_loads_ranked_recommendations_asynchronously(): void
    {
        $user = Account::factory()->create(['role_id' => 4]);
        $viewed = $this->catalogBook('1111111111', 'Viewed book');
        $first = $this->catalogBook('222-2222222', 'First recommendation');
        $second = $this->catalogBook('3333333333', 'Second recommendation');
        Http::fake(['*' => Http::response(['recommendations' => [['isbn' => 3333333333, 'predicted_score' => 1.9], ['isbn' => 2222222222, 'predicted_score' => 1.7], ['isbn' => $viewed->isbn, 'predicted_score' => 1.5], ['isbn' => 'unknown', 'predicted_score' => 1.2]]])]);

        $component = Livewire::actingAs($user)->test(PersonalRecommendations::class, ['bookDetailId' => $viewed->id]);
        Http::assertNothingSent();
        $component->call('loadRecommendations')->assertSet('loaded', true)->assertSee('Second recommendation')->assertSee('First recommendation')
            ->assertDispatched('ml-recommendation-response', fn ($name, $params) => $params['response']['status'] === 200 && count($params['response']['body']['recommendations']) === 4);
        $this->assertSame([$second->id, $first->id], array_column($component->get('books'), 'id'));
        $history = UserHistory::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(UserHistory::RATING_VIEW, $history->rating);
        Http::assertSent(fn ($request) => $request['user_key'] === $user->ml_user_token
            && $request['interaction']['isbn'] === $viewed->isbn
            && $request['interaction']['rating'] === UserHistory::RATING_VIEW
            && ! isset($request['encrypter_id'])
            && ! isset($request['encrypted_book_id']));
    }

    public function test_recommender_failure_is_sent_to_console_and_views_keep_a_stable_user_identifier(): void
    {
        $user = Account::factory()->create(['role_id' => 4]);
        $book = $this->catalogBook('4444444444', 'Another book');
        $existing = UserHistory::upsertHighestRating($user->id, '5555555555', UserHistory::RATING_BORROWED);
        Http::fake(['*' => Http::response([], 503)]);
        Livewire::actingAs($user)->test(PersonalRecommendations::class, ['bookDetailId' => $book->id])
            ->call('loadRecommendations')->assertDontSee('Recommendations are temporarily unavailable.')
            ->assertDispatched('ml-recommendation-response', fn ($name, $params) => $params['response']['status'] === 503 && $params['response']['error'] === 'Recommendations are temporarily unavailable.');
        $this->assertSame($existing->encrypter_id, UserHistory::where('user_id', $user->id)->where('isbn', $book->isbn)->value('encrypter_id'));
    }
}
