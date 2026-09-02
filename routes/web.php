<?php

use App\Mail\WelcomeEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

Route::view('/', 'main.index')->name('index');
Route::get('opac', [\App\Http\Controllers\OpacController::class, 'index'])->name('opac.index');
Route::post('opac/reserve/{bookId}', [\App\Http\Controllers\OpacController::class, 'reserve'])->name('opac.reserve');


Route::middleware(['auth', 'verified', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {

        Route::get('/', function () {
            return redirect()->route('admin.dashboard');
        });

        Route::get('/dashboard', \App\Livewire\Pages\Dashboard\Dashboard::class)->name('dashboard');
        Route::get('/user-management', \App\Livewire\Pages\Dashboard\UserManagement::class)->name('user-management');

        Route::prefix('circulation-desk')->name('circulation-desk.')->group(function () {
            Route::get('/', \App\Livewire\Pages\Dashboard\CirculationDesk::class)->name('index');
            Route::get('/return', \App\Livewire\Pages\Dashboard\CheckInBook::class)->name('return');
            Route::get('/return/confirm', \App\Livewire\Pages\Dashboard\ConfirmReturn::class)->name('return.confirm');
            Route::get('/borrow', \App\Livewire\Pages\Dashboard\CheckOutBook::class)->name('borrow');
        });

        Route::prefix('book-management')->name('book-management.')->group(function () {
            Route::get('/', \App\Livewire\Pages\Dashboard\BookManager::class)->name('index');
        });

        Route::get('/settings', \App\Livewire\Pages\Dashboard\Settings::class)->name('settings');
        // Route::get('/profile', \App\Livewire\Pages\Dashboard\Profile::class)->name('profile');
        // Route::view('/profile', 'livewire.pages.admin.profile')->name('profile');
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
