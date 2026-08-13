<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::middleware(['auth', 'verified', 'role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
        
        Route::view('/dashboard', 'livewire.pages.admin.dashboard')->name('dashboard');
        Route::view('/profile', 'livewire.pages.admin.profile')->name('profile');
});

Route::view('/test', 'test')->name('test');





require __DIR__.'/auth.php';
