<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'pages.homepage.index');

Route::middleware(['auth', 'verified', 'role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
        
        Route::get('/dashboard', \App\Livewire\Pages\dashboard\Dashboard::class)->name('dashboard');
        Route::get('/user-management', \App\Livewire\Pages\dashboard\UserManagement::class)->name('user-management');
        Route::get('/settings', \App\Livewire\Pages\dashboard\Settings::class)->name('settings');
        
        // Route::get('/transaction', \App\Livewire\Pages\dashboard\Transaction::class)->name('transaction');
        
        Route::view('/profile', 'livewire.pages.admin.profile')->name('profile');
        
});

Route::view('/test', 'test')->name('test');

// Route::middleware(['auth', 'verified'])->group(function () {
//     Route::get('/policy/review', \App\Livewire\PolicyReview::class)->name('policy.review');
// });

// Route::middleware(['auth', 'verified', 'check_policy'])->group(function () {
//     // Add user dashboard / authenticated routes here later if they don't exist yet
//     // For now we just wrap the auth.php requirements or the main user flow.
// });

require __DIR__.'/auth.php';
