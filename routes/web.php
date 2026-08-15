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





require __DIR__.'/auth.php';
