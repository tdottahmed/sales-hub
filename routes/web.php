<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/profile/update-image', [ProfileController::class, 'updateImage'])->name('profile.update-image');
});

Route::get('/test', function () {
    $currencyConversion = new \App\Services\CurrencyConversionService();
    dd($currencyConversion->convertToEuro(100, 'BDT'));
});
require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
require __DIR__ . '/frontend.php';
