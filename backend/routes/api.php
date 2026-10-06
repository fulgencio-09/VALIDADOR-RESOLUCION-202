<?php

declare(strict_types=1);

use App\Http\Controllers\Api\CorrectionController;
use App\Http\Controllers\Api\ValidationController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [ValidationController::class, 'health']);
Route::get('/validations', [ValidationController::class, 'index']);
Route::post('/validations', [ValidationController::class, 'store']);
Route::get('/validations/{validationRun}/status', [ValidationController::class, 'status']);
Route::get('/validations/{validationRun}/results', [ValidationController::class, 'results']);
Route::get('/validations/{validationRun}/summary', [ValidationController::class, 'summary']);
Route::get('/validations/{validationRun}/report', [ValidationController::class, 'downloadReport']);
Route::get('/validations/{validationRun}', [ValidationController::class, 'show']);
Route::get('/corrections/catalog', [CorrectionController::class, 'catalog']);
Route::get('/validations/{validationRun}/corrections', [CorrectionController::class, 'history']);
Route::post('/validations/{validationRun}/correct', [CorrectionController::class, 'apply']);
Route::get('/validations/{validationRun}/corrected-download', [CorrectionController::class, 'download']);
