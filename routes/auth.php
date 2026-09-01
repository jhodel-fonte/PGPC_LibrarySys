<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware('unauthenticated')->group(function () {
    Volt::route('register', 'pages.auth.register')
        ->name('register');

    // Student login at the base portal URL
    Volt::route('/student/', 'pages.auth.student-login')
        ->name('login');

    // Employee login at the portal employee URL
    Volt::route('/employee/', 'pages.auth.employee-login')
        ->name('employee.login');


    // Redirects for direct /login and /staff/login visits
    Route::redirect('login', '/student/');
    Route::redirect('staff/login', '/employee/');
    Route::redirect('portal/staff', '/employee/');

    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');

    // Google OAuth
    Route::get('/auth/google', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirect'])
        ->name('auth.google');
    Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'callback'])
        ->name('auth.google.callback');
});

Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');
});
