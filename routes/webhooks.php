<?php

use App\Library\Stripe\Http\Controllers\WebHooks\Controller as StripeController;
use Illuminate\Support\Facades\Route;

Route::post('stripe', [StripeController::class, 'handle'])
    ->name('stripe');
