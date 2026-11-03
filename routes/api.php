<?php

use App\Http\Controllers\Api\FineLookupController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->prefix('v1/fines')->group(function () {
    Route::get('student/{schoolId}', [FineLookupController::class, 'student']);
    Route::get('{fine}', [FineLookupController::class, 'show'])->whereNumber('fine');
});

