<?php
use App\Http\Controllers\LanguageController; 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BarcodeController;
use App\Http\Controllers\Api\TraceabilityController;
use App\Http\Controllers\Api\FDAExportController;
use App\Http\Controllers\Api\ExportVerificationController;

Route::middleware(['auth:sanctum', 'rate.limit.trace', 'package.feature:compliance_report'])->group(function () {
    Route::post('/barcode/scan', [BarcodeController::class, 'scan']);
    Route::post('/barcode/validate', [BarcodeController::class, 'validateTLC']);

    Route::prefix('trace')->name('api.trace.')->group(function () {
        Route::get('/{tlc}', [TraceabilityController::class, 'lookup'])->name('lookup');
        Route::get('/{tlc}/forward', [TraceabilityController::class, 'traceForward'])->name('forward');
        Route::get('/{tlc}/backward', [TraceabilityController::class, 'traceBackward'])->name('backward');
    });

    Route::prefix('fda-export')->group(function () {
        Route::get('/all', [FDAExportController::class, 'exportAll']);
        Route::get('/product/{productId}', [FDAExportController::class, 'exportByProduct']);
        Route::get('/tlc/{tlc}', [FDAExportController::class, 'exportByTLC']);
        Route::post('/validate', [FDAExportController::class, 'validateCompliance']);
        Route::get('/compliance-status', [FDAExportController::class, 'complianceStatus']);
    });

    Route::prefix('verify-export')->group(function () {
        Route::post('/', [ExportVerificationController::class, 'verify']);
        Route::get('/{exportId}', [ExportVerificationController::class, 'getExportDetails']);
        Route::get('/list', [ExportVerificationController::class, 'listExports']);
    });
});

Route::get('/translations/{locale}', [LanguageController::class, 'getTranslations']);
