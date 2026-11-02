<?php

use App\Http\Controllers\Api\StudentAuthController;
use App\Http\Controllers\Api\StudentDashboardController;
use App\Http\Controllers\Api\FineLookupController;
use App\Http\Middleware\EnsureMobileStudent;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->prefix('v1/fines')->group(function () {
    Route::get('student/{schoolId}', [FineLookupController::class, 'student']);
    Route::get('{fine}', [FineLookupController::class, 'show'])->whereNumber('fine');
});

Route::prefix('v1/student')->group(function () {
    Route::post('login', [StudentAuthController::class, 'login'])->middleware('throttle:30,1');
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [StudentAuthController::class, 'logout']);
        Route::get('dashboard', StudentDashboardController::class)->middleware(EnsureMobileStudent::class);
    });
});
