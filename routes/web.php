<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PowerOfAttorneyController;


Route::get('/', function () {
    return redirect('/user');
});

Route::middleware('auth')->group(function () {
    Route::get('/procuracoes/{powerOfAttorney}/pdf', [PowerOfAttorneyController::class, 'pdf'])
        ->name('power-of-attorney.pdf');
});
