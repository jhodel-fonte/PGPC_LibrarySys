<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
Route::middleware('unauthenticated')->group(function () {

    // Student Registration
    Route::get('/register', \App\Livewire\Pages\Auth\Register::class)->name('register');

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
    Route::redirect('admin/login', '/employee/');
    Route::redirect('librarian/login', '/employee/');
});

// 401 Error Page for deactivated / disabled accounts
Route::get('/account/deactivated', function () {
    if (request()->expectsJson()) {
        return response()->json([
            'error' => [
                'type' => 'invalid_request_error',
                'code' => 'account_deactivated',
                'message' => 'You do not have an account because it has been deleted or deactivated.',
            ],
        ], 401);
    }

    return response()->view('errors.401', [
        'code' => 401,
        'errorCode' => 'account_deactivated',
        'errorType' => 'invalid_request_error',
        'title' => 'Account Deactivated',
        'bodyTitle' => 'Account Deactivated',
        'message' => 'You do not have an account because it has been deleted or deactivated.',
    ], 401);
})->name('account.deactivated');

Route::redirect('/account-deactivated', '/account/deactivated');

Route::middleware('auth')->group(function () {
    // Student Account Verification & Registration Status Pages
    Route::get('/verify', \App\Livewire\Pages\Auth\Verify::class)->name('verification.upload');
    Route::get('/registration-status', \App\Livewire\Pages\Auth\RegistrationStatus::class)->name('verification.status');

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
