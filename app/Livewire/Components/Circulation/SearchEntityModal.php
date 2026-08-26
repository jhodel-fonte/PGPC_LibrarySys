<?php

namespace App\Livewire\Components\Circulation;

use Livewire\Component;
use App\Models\Student;
use App\Models\Book;
use Livewire\Attributes\On;

class SearchEntityModal extends Component
{
    public $isOpen = false;
    public $searchQuery = '';
    public $activeTab = 'all'; // all, members, books
    public $results = [];
    public $memberCount = 0;
    public $bookCount = 0;
    public $perPage = 5;

    #[On('open-search-modal')]
    public function openModal()
    {
        $this->isOpen = true;
        $this->resetSearch();
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetSearch();
    }

    public function resetSearch()
    {
        $this->searchQuery = '';
        $this->activeTab = 'all';
        $this->results = [];
        $this->memberCount = 0;
        $this->bookCount = 0;
        $this->perPage = 5;
        $this->dispatch('search-completed');
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->perPage = 5; // Reset pagination when switching tabs
        $this->performSearch();
    }

    public function updatedSearchQuery()
    {
        $this->perPage = 5; // Reset pagination when query changes
        $this->performSearch();
    }

    public function loadMore()
    {
        $this->perPage += 5; // Load 5 more records
        $this->performSearch();
    }

    public function performSearch()
    {
        $rawQuery = trim($this->searchQuery);
        if (strlen($rawQuery) < 2) {
            $this->results = [];
            $this->memberCount = 0;
            $this->bookCount = 0;
            $this->dispatch('search-completed');
            return;
        }

        $query = \App\Services\SanitizeInput::escapeLike($rawQuery);

        // Build member query to count matches
        $membersQuery = Student::where('school_id_number', 'ilike', "%{$query}%")
            ->orWhere('first_name', 'ilike', "%{$query}%")
            ->orWhere('middle_name', 'ilike', "%{$query}%")
            ->orWhere('last_name', 'ilike', "%{$query}%");
        $this->memberCount = $membersQuery->count();

        // Build book query to count matches
        $booksQuery = Book::where('barcode', 'ilike', "%{$query}%")
            ->orWhere('accession_number', 'ilike', "%{$query}%")
            ->orWhereHas('bookDetail.bookData', function($q) use ($query) {
                $q->where('book_title', 'ilike', "%{$query}%");
            });
        $this->bookCount = $booksQuery->count();

        $results = [];

        // 1. Fetch Members if tab matches
        if ($this->activeTab === 'all' || $this->activeTab === 'members') {
            $students = $membersQuery->with(['libraryStatus', 'account'])->limit($this->perPage)->get();
            foreach ($students as $student) {
                $results[] = [
                    'type' => 'member',
                    'id' => $student->id,
                    'code' => $student->school_id_number,
                    'title' => trim($student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name),
                    'subtitle' => trim(($student->program ?? '') . ' - ' . ($student->year_level ?? '')),
                    'email' => $student->account ? $student->account->email : '',
                    'status' => $student->libraryStatus ? $student->libraryStatus->status : 'Active',
                    'icon' => 'user'
                ];
            }
        }

        // 2. Fetch Books if tab matches
        if ($this->activeTab === 'all' || $this->activeTab === 'books') {
            $books = $booksQuery->with(['bookDetail.bookData.authors'])->limit($this->perPage)->get();
            foreach ($books as $book) {
                $detail = $book->bookDetail;
                $data = $detail ? $detail->bookData : null;
                $authorName = 'Unknown Author';
                if ($data && $data->authors->isNotEmpty()) {
                    $authorName = $data->authors->map(function($a) {
                        return trim($a->first_name . ' ' . $a->last_name);
                    })->implode(', ');
                }

                // Get book code initials (e.g. DBMS, SAD, REF)
                $titleWords = explode(' ', $data ? $data->book_title : 'BOOK');
                $codeInitials = '';
                foreach ($titleWords as $word) {
                    $codeInitials .= strtoupper(substr($word, 0, 1));
                    if (strlen($codeInitials) >= 4) break;
                }

                $results[] = [
                    'type' => 'book',
                    'id' => $book->id,
                    'code' => $book->accession_number ?: $book->barcode,
                    'title' => $data ? $data->book_title : 'Unknown Book',
                    'subtitle' => trim($authorName . ' · Accession No. ' . ($book->accession_number ?? 'N/A')),
                    'barcode' => $book->barcode,
                    'code_tag' => $codeInitials ?: 'BOOK',
                    'icon' => 'book-open'
                ];
            }
        }

        $this->results = $results;
        $this->dispatch('search-completed');
    }

    public function selectEntity($type, $code)
    {
        // Dispatch search-code to parent CheckInBook controller
        $this->dispatch('search-code', code: $code);
        
        // Also populate the QrSearchBar input so the user sees the code
        $this->dispatch('set-search-value', code: $code);

        // Close the modal
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.components.circulation.search-entity-modal');
    }
}
