<?php

use App\Http\Controllers\Api\StudentAuthController;
use App\Http\Controllers\Api\StudentDashboardController;
use App\Http\Middleware\EnsureMobileStudent;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/student')->group(function () {
    Route::post('login', [StudentAuthController::class, 'login'])->middleware('throttle:30,1');
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [StudentAuthController::class, 'logout']);
        Route::get('dashboard', StudentDashboardController::class)->middleware(EnsureMobileStudent::class);
    });
});
