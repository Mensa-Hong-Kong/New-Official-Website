<?php

use App\Http\Controllers\WebHooks\StripeController;
use Illuminate\Support\Facades\Route;

Route::post('stripe', [StripeController::class, 'handle'])
    ->name('stripe');
