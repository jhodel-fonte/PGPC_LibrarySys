<?php

namespace Tests\Feature;

use App\Livewire\Forms\AddBookForm;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CatalogAddValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('books', function (Blueprint $t) { $t->id(); $t->string('accession_number'); $t->string('code')->nullable(); });
    }

    public function test_periodical_requires_an_issue_identifier(): void
    {
        $form = new AddBookForm();
        $form->catalogKind = 'journal';
        $form->bookTitle = 'Journal';
        $form->accessionNumber = 'ISSUE-1';
        try {
            $form->save();
            $this->fail('An issue identifier is required.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('issueNumber', $e->errors());
        }
    }

    public function test_hidden_fields_are_excluded_from_equipment_validation(): void
    {
        $form = new AddBookForm();
        $form->catalogKind = 'equipment';
        $form->bookTitle = 'Calculator';
        $form->accessionNumber = 'ITEM-1';
        $form->issueDate = 'invalid date';
        $form->issn = 'invalid';
        $form->authorId = 999;
        $form->save();
        // The deliberately incomplete schema triggers the generic save error after validation.
        $this->assertSame('A database error occurred while saving the book. Please try again.', $form->errorMessage);
    }

    public function test_invalid_status_is_rejected(): void
    {
        $form = new AddBookForm();
        $form->catalogKind = 'equipment';
        $form->bookTitle = 'Calculator';
        $form->accessionNumber = 'ITEM-1';
        $form->status = 'Unknown';
        try {
            $form->save();
            $this->fail('An invalid status must be rejected.');
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('status', $e->errors());
        }
    }
}
