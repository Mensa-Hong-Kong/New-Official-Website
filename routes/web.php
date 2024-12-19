<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::view('/', 'layouts.app')->name('index');
Route::middleware('guest')->group(function () {
    Route::get('register', [UserController::class, 'create'])->name('register');
    Route::post('register', [UserController::class, 'store']);
    Route::view('login', 'authentication.login')->name('login');
    Route::post('login', [UserController::class, 'login']);
});

Route::any('logout', [UserController::class, 'logout'])->name('logout');
Route::middleware('auth')->group(function () {
    Route::singleton('profile', UserController::class)
        ->except('edit')
        ->destroyable();
    Route::get('contacts/{contact}/send-verify-code', [ContactController::class, 'sendVerifyCode'])
        ->name('contacts.send-verify-code');
    Route::post('contacts/{contact}/verify', [ContactController::class, 'verify'])
        ->name('contacts.verify');
});
