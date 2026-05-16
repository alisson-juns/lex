<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PowerOfAttorneyController;
use App\Http\Controllers\EnterprisePowerOfAttorneyController;
use App\Http\Controllers\FeeAgreementController;

Route::get('/', function () {
    return redirect('/user');
});

Route::middleware('auth')->group(function () {
    Route::get('/procuracoes/{powerOfAttorney}/pdf', [PowerOfAttorneyController::class, 'pdf'])
        ->name('power-of-attorney.pdf');

    Route::get('/procuracoes/empresa/{enterprisePowerOfAttorney}/pdf', [EnterprisePowerOfAttorneyController::class, 'pdf'])
        ->name('enterprise-power-of-attorney.pdf');

    Route::get('/contratos/{feeAgreement}/pdf', [FeeAgreementController::class, 'pdf'])
        ->name('fee-agreement.pdf');
});
