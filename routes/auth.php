<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('unauthenticated')->group(function () {

    Volt::route('register', 'pages.auth.register')->name('register');

    // Student login
    Route::get('/student/', \App\Livewire\Pages\Auth\StudentLogin::class)->name('login');
    // Employee login
    Route::get('/employee/', \App\Livewire\Pages\Auth\EmployeeLogin::class)->name('employee.login');
    Route::get('forgot-password', \App\Livewire\Pages\Auth\ForgotPassword::class)->name('password.request');
    Volt::route('reset-password/{token}', 'pages.auth.reset-password')->name('password.reset');

    // Google OAuth
    Route::get('/auth/google', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'callback'])->name('auth.google.callback');

    // Redirects for direct /login and /staff/login visits
    Route::redirect('login', '/student/');
    Route::redirect('staff/login', '/employee/');
    Route::redirect('portal/staff', '/employee/');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Volt::route('confirm-password', 'pages.auth.confirm-password')->name('password.confirm');
});

Route::match(['get', 'post'], '/logout', function (\Illuminate\Http\Request $request) {
    $user = \Illuminate\Support\Facades\Auth::user();
    $roleName = strtolower(str_replace(' ', '', $user?->role?->name ?? ''));
    $isEmployee = in_array($roleName, ['admin', 'headlibrarian', 'librarian'], true);

    \Illuminate\Support\Facades\Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect($isEmployee ? route('employee.login') : route('login'));
})->name('logout');
