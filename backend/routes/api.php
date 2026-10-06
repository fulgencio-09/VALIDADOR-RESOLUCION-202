<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ValidationController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [ValidationController::class, 'health']);
Route::get('/validations', [ValidationController::class, 'index']);
Route::get('/validations/{validationRun}', [ValidationController::class, 'show']);
Route::post('/validations', [ValidationController::class, 'store']);
