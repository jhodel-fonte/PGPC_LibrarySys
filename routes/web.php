<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified', 'role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
        
        Route::get('/dashboard', \App\Livewire\Pages\Admin\Dashboard::class)->name('dashboard');
        Route::get('/user-management', \App\Livewire\Pages\Admin\UserManagement::class)->name('user-management');
        Route::get('/settings', \App\Livewire\Pages\Admin\Settings::class)->name('settings');
        Route::view('/profile', 'livewire.pages.admin.profile')->name('profile');
});

Route::view('/test', 'test')->name('test');
// Route::view('/circulation', 'circulation.index')->name('circulation.index');



Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/policy/review', \App\Livewire\PolicyReview::class)->name('policy.review');
});

Route::middleware(['auth', 'verified', 'check_policy'])->group(function () {
    // Add user dashboard / authenticated routes here later if they don't exist yet
    // For now we just wrap the auth.php requirements or the main user flow.
});

require __DIR__.'/auth.php';
