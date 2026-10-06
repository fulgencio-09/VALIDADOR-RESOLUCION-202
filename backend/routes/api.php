<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ValidationController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [ValidationController::class, 'health']);
Route::post('/validations', [ValidationController::class, 'store']);
