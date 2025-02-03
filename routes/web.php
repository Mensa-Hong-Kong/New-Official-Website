<?php

use App\Http\Controllers\Admin\AdmissionTest\Controller as AdminAdmissionTestController;
use App\Http\Controllers\Admin\AdmissionTest\ProctorController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\TeamTypeController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\IsAdministrator;
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

Route::get('/', function () {
    return view('home');
})->name('index');

Route::middleware('guest')->group(function () {
    Route::get('register', [UserController::class, 'create'])->name('register');
    Route::post('register', [UserController::class, 'store']);
    Route::get('/login', [UserController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [UserController::class, 'login']);
    Route::get('forget-password', [UserController::class, 'forgetPassword'])
        ->name('forget-password');
    Route::match(['put', 'patch'], 'reset-password', [UserController::class, 'resetPassword'])
        ->name('reset-password');

    Route::get('/about', function () {
        return view('about');
    })->name('about');

    Route::get('/join', function () {
        return view('join');
    })->name('join');

    Route::get('/events', function () {
        return view('events');
    })->name('events');

    Route::get('/contact', function () {
        return view('contact');
    })->name('contact');

    Route::get('/test', function () {
        return view('test');
    })->name('test');

    Route::get('/benefits', function () {
        return view('benefits');
    })->name('benefits');
});

Route::post('/logout', [UserController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [UserController::class, 'show'])->name('profile.show');
    Route::put('/profile', [UserController::class, 'update'])->name('profile.update');

    Route::get('contacts/{contact}/send-verify-code', [ContactController::class, 'sendVerifyCode'])
        ->name('contacts.send-verify-code');
    Route::post('contacts/{contact}/verify', [ContactController::class, 'verify'])
        ->name('contacts.verify');
    Route::match(['put', 'patch'], 'contacts/{contact}/set-default', [ContactController::class, 'setDefault'])
        ->name('contacts.set-default');
    Route::resource('/contacts', ContactController::class)
        ->only(['store', 'update', 'destroy']);

    Route::get('/about', function () {
        return view('about');
    })->name('about');

    Route::get('/join', function () {
        return view('join');
    })->name('join');

    Route::get('/events', function () {
        return view('events');
    })->name('events');

    Route::get('/contact', function () {
        return view('contact');
    })->name('contact');

    Route::get('/test', function () {
        return view('test');
    })->name('test');

    Route::get('/benefits', function () {
        return view('benefits');
    })->name('benefits');

    Route::prefix('admin')->name('admin.')
        ->middleware(IsAdministrator::class)
        ->group(function () {
            Route::view('/', 'admin.index')->name('index');
            Route::resource('users', AdminUserController::class)
                ->only(['index', 'show', 'update']);
            Route::match(['put', 'patch'], 'users/{user}/password', [AdminUserController::class, 'resetPassword'])
                ->name('users.reset-password');
            Route::resource('contacts', AdminContactController::class)
                ->only(['store', 'update', 'destroy']);
            Route::match(['put', 'patch'], 'contacts/{contact}/verify', [AdminContactController::class, 'verify'])
                ->name('contacts.verify');
            Route::match(['put', 'patch'], 'contacts/{contact}/default', [AdminContactController::class, 'default'])
                ->name('contacts.default');
            Route::match(['put', 'patch'], 'team-types/display-order', [TeamTypeController::class, 'displayOrder'])
                ->name('team-types.display-order.update');
            Route::resource('team-types', TeamTypeController::class)
                ->only(['index', 'update']);
            Route::match(['put', 'patch'], 'teams/display-order', [TeamController::class, 'displayOrder'])
                ->name('teams.display-order.update');
            Route::resource('teams', TeamController::class);
            Route::match(['put', 'patch'], 'teams/{team}/roles/display-order', [RoleController::class, 'displayOrder'])
                ->name('teams.roles.display-order.update');
            Route::resource('teams/{team}/roles', RoleController::class)
                ->except(['index', 'show'])
                ->names('teams.roles');
            Route::match(['put', 'patch'], 'modules/display-order', [ModuleController::class, 'displayOrder'])
                ->name('modules.display-order.update');
            Route::resource('modules', ModuleController::class)
                ->only(['index', 'update']);
            Route::match(['put', 'patch'], 'permissions/display-order', [PermissionController::class, 'displayOrder'])
                ->name('permissions.display-order.update');
            Route::resource('permissions', PermissionController::class)
                ->only(['index', 'update']);
            Route::resource('admission-tests', AdminAdmissionTestController::class)
                ->except(['edit', 'destroy']);
            Route::prefix('admission-tests/{admission_test}')->name('admission-tests.')->group(
                function () {
                    Route::resource('proctors', ProctorController::class)
                        ->only(['store', 'update', 'destroy']);
                }
            );
        });
});
