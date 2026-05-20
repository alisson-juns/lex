<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PowerOfAttorneyController;
use App\Http\Controllers\EnterprisePowerOfAttorneyController;
use App\Http\Controllers\FeeAgreementController;
use App\Http\Controllers\EnterpriseFeeAgreementController;
use App\Http\Controllers\GratuityDeclarationController;
use App\Http\Controllers\GoogleAuthController;

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

    Route::get('/contratos/empresa/{enterpriseFeeAgreement}/pdf', [EnterpriseFeeAgreementController::class, 'pdf'])
        ->name('enterprise-fee-agreement.pdf');

    Route::get('/declaracoes/{gratuityDeclaration}/pdf', [GratuityDeclarationController::class, 'pdf'])
        ->name('gratuity-declaration.pdf');

    Route::get('/google/redirect', [GoogleAuthController::class, 'redirect'])
        ->name('google.redirect');
    Route::get('/google/callback', [GoogleAuthController::class, 'callback'])
        ->name('google.callback');
    Route::get('/google/disconnect', [GoogleAuthController::class, 'disconnect'])
        ->name('google.disconnect');


});
