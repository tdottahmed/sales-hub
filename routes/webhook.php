<?php

use App\Http\Controllers\DriffleWebhookController;

Route::post('/reservation', [DriffleWebhookController::class, 'reservation']);
Route::post('/provision', [DriffleWebhookController::class, 'provision']);
Route::post('/cancellation', [DriffleWebhookController::class, 'cancellation']);
