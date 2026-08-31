<?php

use Illuminate\Support\Facades\Route;

// Route::view('/', 'pages.homepage.index');
Route::view('/', 'main.homepage')->name('index');



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

        Route::get('/settings', \App\Livewire\Pages\Dashboard\Settings::class)->name('settings');
        // Route::get('/profile', \App\Livewire\Pages\Dashboard\Profile::class)->name('profile');
        // Route::view('/profile', 'livewire.pages.admin.profile')->name('profile');
});













// Route::middleware(['auth', 'verified'])->group(function () {
//     Route::get('/policy/review', \App\Livewire\PolicyReview::class)->name('policy.review');
// });

// Route::middleware(['auth', 'verified', 'check_policy'])->group(function () {
//     // Add user dashboard / authenticated routes here later if they don't exist yet
//     // For now we just wrap the auth.php requirements or the main user flow.
// });

require __DIR__.'/auth.php';
