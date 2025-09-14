<?php

use App\Http\Controllers\Admin\ApplicationSetupController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\ProductController;

Route::group(['middleware' => ['role:super-admin|admin|staff|user']], function () {
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class)->except('show');
    Route::resource('users', UserController::class);

    Route::get('dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('settings/organization', [ApplicationSetupController::class, 'index'])->name('applicationSetup.index');
    Route::post('settings/organization', [ApplicationSetupController::class, 'update'])->name('applicationSetup.update');
    Route::post('settings/update-env', [ApplicationSetupController::class, 'updateEnv'])->name('settings.updateEnv');
    Route::post('settings/update-profit-margin', [ApplicationSetupController::class, 'updateProfitMargin'])->name('settings.updateProfitMargin');


    Route::get('supplier-products', [ProductController::class, 'index'])->name('supplierProducts.index');
    Route::get('supplier-products/show/{product}', [ProductController::class, 'show'])->name('supplierProducts.show');

    Route::get('driffle-products', [ProductController::class, 'driffleProducts'])->name('driffleProducts.index');
    Route::get('driffle-products/show/{driffleProduct}', [ProductController::class, 'driffleProductsShow'])->name('driffleProducts.show');

    Route::get('driffle/map-products', [ProductController::class, 'driffleMapProducts'])->name('driffleProducts.mapProducts');
    Route::get('driffle/create-offer/{similarProduct}', [ProductController::class, 'createOffer'])->name('driffleProducts.createOffer');

});
