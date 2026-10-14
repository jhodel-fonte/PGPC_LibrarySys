<?php

use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::view('/', 'main.index')->name('index');
Route::get('opac', [\App\Http\Controllers\OpacController::class, 'index'])->name('opac.index');
Route::get('opac/advanced-search', [\App\Http\Controllers\OpacController::class, 'advancedSearch'])->name('opac.advanced');
Route::get('opac/book/{identifier?}', \App\Livewire\Pages\Main\Book::class)->name('opac.book.detail');
Route::post('opac/reserve/{bookId}', [\App\Http\Controllers\OpacController::class, 'reserve'])->name('opac.reserve');


Route::middleware(['auth', 'verified', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {

        Route::get('/', function () {
            return redirect()->route('admin.dashboard');
        });

        Route::get('/dashboard', \App\Livewire\Pages\Dashboard\Dashboard::class)->name('dashboard');
        Route::get('/user-management', \App\Livewire\Pages\Dashboard\UserManagement::class)->name('user-management');
        Route::get('/user-management/add', \App\Livewire\Pages\Dashboard\AddUser::class)->name('user-management.add');
        Route::get('/user-management/edit/{id}', \App\Livewire\Pages\Dashboard\EditUserPage::class)->name('user-management.edit');
        Route::get('/user-management/{id}/edit', \App\Livewire\Pages\Dashboard\EditUserPage::class);
        Route::get('/user-management/{id}', \App\Livewire\Pages\Dashboard\UserDetails::class)->name('user-details');

        Route::prefix('circulation-desk')->name('circulation-desk.')->group(function () {
            Route::get('/', \App\Livewire\Pages\Dashboard\CirculationDesk::class)->name('index');
            Route::get('/return', \App\Livewire\Pages\Dashboard\CheckInBook::class)->name('return');
            Route::get('/return/confirm', \App\Livewire\Pages\Dashboard\ConfirmReturn::class)->name('return.confirm');
            Route::get('/borrow', \App\Livewire\Pages\Dashboard\CheckOutBook::class)->name('borrow');
        });

        Route::prefix('book-management')->name('book-management.')->group(function () {
            Route::get('/', \App\Livewire\Pages\Dashboard\BookManager::class)->name('index');
            Route::get('/add', \App\Livewire\Pages\Dashboard\AddBook::class)->name('add');
            Route::get('/edit/{id}', \App\Livewire\Pages\Dashboard\EditBook::class)->name('edit');
        });

        Route::get('/authors/search', [\App\Http\Controllers\AuthorController::class, 'search'])->name('authors.search');
        Route::get('/publishers/search', [\App\Http\Controllers\PublisherController::class, 'search'])->name('publishers.search');

        Route::get('/settings', \App\Livewire\Pages\Dashboard\Settings::class)->name('settings');
});

Route::middleware(['auth', 'verified', 'role:Librarian,Head Librarian,Admin'])->prefix('librarian')->name('librarian.')->group(function () {

        Route::get('/', function () {
            return redirect()->route('librarian.dashboard');
        });

        Route::get('/dashboard', \App\Livewire\Pages\Dashboard\Dashboard::class)->name('dashboard');

        Route::middleware('permission:manage_users,view_students,manage_librarians')->group(function () {
            Route::get('/user-management', \App\Livewire\Pages\Dashboard\UserManagement::class)->name('user-management');
            Route::get('/user-management/add', \App\Livewire\Pages\Dashboard\AddUser::class)->name('user-management.add')->middleware('permission:manage_users,create_students,manage_librarians');
            Route::get('/user-management/edit/{id}', \App\Livewire\Pages\Dashboard\EditUserPage::class)->name('user-management.edit')->middleware('permission:manage_users,edit_students,manage_librarians');
            Route::get('/user-management/{id}/edit', \App\Livewire\Pages\Dashboard\EditUserPage::class)->middleware('permission:manage_users,edit_students,manage_librarians');
            Route::get('/user-management/{id}', \App\Livewire\Pages\Dashboard\UserDetails::class)->name('user-details');
        });

        Route::middleware('permission:manage_circulation,view_circulation')->prefix('circulation-desk')->name('circulation-desk.')->group(function () {
            Route::get('/', \App\Livewire\Pages\Dashboard\CirculationDesk::class)->name('index');
            Route::get('/return', \App\Livewire\Pages\Dashboard\CheckInBook::class)->name('return')->middleware('permission:manage_circulation,return_book');
            Route::get('/return/confirm', \App\Livewire\Pages\Dashboard\ConfirmReturn::class)->name('return.confirm')->middleware('permission:manage_circulation,return_book');
            Route::get('/borrow', \App\Livewire\Pages\Dashboard\CheckOutBook::class)->name('borrow')->middleware('permission:manage_circulation,borrow_book');
        });

        Route::middleware('permission:manage_catalog,view_catalog')->prefix('book-management')->name('book-management.')->group(function () {
            Route::get('/', \App\Livewire\Pages\Dashboard\BookManager::class)->name('index');
            Route::get('/add', \App\Livewire\Pages\Dashboard\AddBook::class)->name('add')->middleware('permission:manage_catalog,create_book');
            Route::get('/edit/{id}', \App\Livewire\Pages\Dashboard\EditBook::class)->name('edit')->middleware('permission:manage_catalog,edit_book');
        });

        Route::get('/authors/search', [\App\Http\Controllers\AuthorController::class, 'search'])->name('authors.search');
        Route::get('/publishers/search', [\App\Http\Controllers\PublisherController::class, 'search'])->name('publishers.search');

        Route::get('/settings', \App\Livewire\Pages\Dashboard\Settings::class)->name('settings')->middleware('permission:manage_settings');
});

Route::get('/send-test-email', function () {
    $recipient = 'jhcyrene@gmail.com';

    try {
        Mail::to($recipient)->send(new WelcomeEmail());
        return "Email sent successfully to {$recipient}!";
    } catch (\Throwable $e) {
        return "Failed to send email. Error: " . $e->getMessage();
    }
});

Route::get('/preview-reset-email', function () {
    return new \App\Mail\ResetEmail(url('/reset-password/sample-token?email=student@pgpc.edu.ph'));
});















// Route::middleware(['auth', 'verified'])->group(function () {
//     Route::get('/policy/review', \App\Livewire\PolicyReview::class)->name('policy.review');
// });

// Route::middleware(['auth', 'verified', 'check_policy'])->group(function () {
//     // Add user dashboard / authenticated routes here later if they don't exist yet
//     // For now we just wrap the auth.php requirements or the main user flow.
// });

require __DIR__.'/auth.php';
