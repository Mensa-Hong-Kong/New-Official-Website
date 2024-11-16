<?php

use App\Http\Controllers\EmailController;
use App\Http\Controllers\MobileController;
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

Route::middleware('auth')->group(function () {
    Route::post('/emails/{email}/verification', [EmailController::class,'verification'])
        ->middleware('throttle:1,1')
        ->name('email.verification');
    Route::match(['put', 'patch'], '/emails/{email}/verify', [EmailController::class,'verification'])->name('email.verify');
    Route::post('/mobiles/{mobile}/verification', [MobileController::class,'verification'])
        ->middleware('throttle:1,1')
        ->name('mobile.verification');
    Route::match(['put', 'patch'], '/mobiles/{mobile}/verify', [MobileController::class,'verify'])->name('mobile.verify');
});

Route::any('logout', [UserController::class, 'logout'])->name('logout');
Route::middleware('auth')->group(function () {
    Route::singleton('profile', UserController::class)
        ->except('edit')
        ->destroyable();
});
