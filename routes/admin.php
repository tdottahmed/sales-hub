<?php

use App\Http\Controllers\Admin\ApplicationSetupController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PermissionController;

Route::group(['middleware' => ['role:super-admin|admin|staff|user']], function () {
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class)->except('show');
    Route::resource('users', UserController::class);

    Route::get('dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('settings/organization', [ApplicationSetupController::class, 'index'])->name('applicationSetup.index');
    Route::post('settings/organization', [ApplicationSetupController::class, 'update'])->name('applicationSetup.update');
    Route::post('settings/update-env', [ApplicationSetupController::class, 'updateEnv'])->name('settings.updateEnv');
    Route::post('settings/update-profit-margin', [ApplicationSetupController::class, 'updateProfitMargin'])->name('settings.updateProfitMargin');

});
