<?php

namespace Tests\Feature;

use App\Livewire\Forms\AddBookForm;
use App\Models\Account;
use App\Models\Book;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AddBookInterfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_submit_the_add_book_form_and_see_success(): void
    {
        $admin = Account::factory()->create(['role_id' => Role::where('name', 'Admin')->value('id')]);

        $form = Livewire::actingAs($admin)->test(AddBookForm::class)
            ->assertSee('Book Title')
            ->assertSee('Accession Number')
            ->assertSee('Save Book')
            ->set('bookTitle', 'Interface Testing in Libraries')
            ->set('authorSearch', 'Alex Reader')
            ->set('callNumber', 'QA123 R43')
            ->set('accessionNumber', 'INTERFACE-BOOK-001')
            ->set('barcode', 'INTERFACE-BARCODE-001')
            ->set('location', 'Shelf A1')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('showFeedback', true)
            ->assertSee('Book added')
            ->assertSee('Interface Testing in Libraries');

        $book = Book::where('accession_number', 'INTERFACE-BOOK-001')->firstOrFail();
        $this->assertSame('INTERFACE-BARCODE-001', $book->code);
        $this->assertSame('Shelf A1', $book->location);
        $this->assertSame('QA123 R43', $book->bookDetail->call_number);
        $this->assertSame('Interface Testing in Libraries', $book->bookDetail->bookData->book_title);
        $this->assertTrue($book->bookDetail->bookData->authors()->where('first_name', 'Alex')->where('last_name', 'Reader')->exists());

        $form->call('submit');
        $this->assertSame(1, Book::where('accession_number', 'INTERFACE-BOOK-001')->count());
    }

    public function test_incomplete_form_shows_errors_and_keeps_entered_title(): void
    {
        $admin = Account::factory()->create(['role_id' => Role::where('name', 'Admin')->value('id')]);

        Livewire::actingAs($admin)->test(AddBookForm::class)
            ->set('bookTitle', 'Unfinished Book')
            ->call('submit')
            ->assertHasErrors(['accessionNumber', 'callNumber'])
            ->assertSet('showFeedback', true)
            ->assertSet('bookTitle', 'Unfinished Book')
            ->assertSee('Unable to save book');

        $this->assertDatabaseMissing('book_datas', ['book_title' => 'Unfinished Book']);
    }
}
