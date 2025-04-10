<?php

use App\Http\Controllers\Admin\AdmissionTest\CandidateController as AdminCandidateController;
use App\Http\Controllers\Admin\AdmissionTest\Controller as AdminAdmissionTestController;
use App\Http\Controllers\Admin\AdmissionTest\ProctorController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Admin\CustomPageController as AdmissionCustomPageController;
use App\Http\Controllers\Admin\ModuleController;
use App\Http\Controllers\Admin\NavigationItemController as AdmissionNavigationItemController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SiteContentController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\Admin\TeamTypeController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PageController;
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

Route::view('/', 'layouts.app')->name('index');
Route::middleware('guest')->group(function () {
    Route::get('register', [UserController::class, 'create'])->name('register');
    Route::post('register', [UserController::class, 'store']);
    Route::view('login', 'user.login')->name('login');
    Route::post('login', [UserController::class, 'login']);
    Route::get('forget-password', [UserController::class, 'forgetPassword'])
        ->name('forget-password');
    Route::match(['put', 'patch'], 'reset-password', [UserController::class, 'resetPassword'])
        ->name('reset-password');
});

Route::get('admission-tests', [PageController::class, 'admissionTests'])
    ->name('admission-tests.index');

Route::any('logout', [UserController::class, 'logout'])->name('logout');
Route::middleware('auth')->group(function () {
    Route::singleton('profile', UserController::class)
        ->except('edit', 'destroy')
        ->destroyable();
    Route::get('contacts/{contact}/send-verify-code', [ContactController::class, 'sendVerifyCode'])
        ->name('contacts.send-verify-code')
        ->whereNumber('contact');
    Route::post('contacts/{contact}/verify', [ContactController::class, 'verify'])
        ->name('contacts.verify')
        ->whereNumber('contact');
    Route::match(['put', 'patch'], 'contacts/{contact}/set-default', [ContactController::class, 'setDefault'])
        ->name('contacts.set-default')
        ->whereNumber('contact');
    Route::resource('/contacts', ContactController::class)
        ->only(['store', 'update', 'destroy'])
        ->whereNumber('contact');
    Route::singleton('admission-tests/{admission_test}/candidates', CandidateController::class)
        ->creatable()
        ->except(['edit', 'update'])
        ->whereNumber('admission_test')
        ->names('admission-tests.candidates');

    Route::prefix('admin')->name('admin.')
        ->group(function () {
            Route::middleware(IsAdministrator::class)
                ->group(function () {
                    Route::view('/', 'admin.index')->name('index');
                    Route::match(['put', 'patch'], 'team-types/display-order', [TeamTypeController::class, 'displayOrder'])
                        ->name('team-types.display-order.update');
                    Route::resource('team-types', TeamTypeController::class)
                        ->only(['index', 'update'])
                        ->whereNumber('team_type');
                    Route::match(['put', 'patch'], 'teams/display-order', [TeamController::class, 'displayOrder'])
                        ->name('teams.display-order.update');
                    Route::resource('teams', TeamController::class)
                        ->whereNumber('team');
                    Route::match(['put', 'patch'], 'teams/{team}/roles/display-order', [RoleController::class, 'displayOrder'])
                        ->name('teams.roles.display-order.update')
                        ->whereNumber('team');
                    Route::resource('teams/{team}/roles', RoleController::class)
                        ->except(['index', 'show'])
                        ->names('teams.roles')
                        ->whereNumber(['team', 'role']);
                    Route::match(['put', 'patch'], 'modules/display-order', [ModuleController::class, 'displayOrder'])
                        ->name('modules.display-order.update');
                    Route::resource('modules', ModuleController::class)
                        ->only(['index', 'update'])
                        ->whereNumber('module');
                    Route::match(['put', 'patch'], 'permissions/display-order', [PermissionController::class, 'displayOrder'])
                        ->name('permissions.display-order.update');
                    Route::resource('permissions', PermissionController::class)
                        ->only(['index', 'update'])
                        ->whereNumber('permission');
                });
            Route::resource('users', AdminUserController::class)
                ->only(['index', 'show', 'update'])
                ->whereNumber('user');
            Route::match(['put', 'patch'], 'users/{user}/password', [AdminUserController::class, 'resetPassword'])
                ->name('users.reset-password')
                ->whereNumber('user');
            Route::resource('contacts', AdminContactController::class)
                ->only(['store', 'update', 'destroy'])
                ->whereNumber('contact');
            Route::match(['put', 'patch'], 'contacts/{contact}/verify', [AdminContactController::class, 'verify'])
                ->name('contacts.verify')
                ->whereNumber('contact');
            Route::match(['put', 'patch'], 'contacts/{contact}/default', [AdminContactController::class, 'default'])
                ->name('contacts.default')
                ->whereNumber('contact');
            Route::resource('admission-tests', AdminAdmissionTestController::class)
                ->except(['edit'])
                ->whereNumber('admission_test');
            Route::prefix('admission-tests/{admission_test}')->name('admission-tests.')->group(
                function () {
                    Route::resource('proctors', ProctorController::class)
                        ->only(['store', 'update', 'destroy'])
                        ->whereNumber('proctor');
                    Route::match(['put', 'patch'], '/candidates/{candidate}/present', [AdminCandidateController::class, 'present'])
                        ->name('candidates.present')
                        ->whereNumber('candidate');
                    Route::match(['put', 'patch'], '/candidates/{candidate}/result', [AdminCandidateController::class, 'result'])
                        ->name('candidates.result')
                        ->whereNumber('candidate');
                    Route::resource('candidates', AdminCandidateController::class)
                        ->except('index', 'create')
                        ->whereNumber('candidate');
                }
            )->whereNumber(['admission_test', 'proctor']);
            Route::resource('site-contents', SiteContentController::class)
                ->only(['index', 'edit', 'update'])
                ->whereNumber('site_content');
            Route::resource('custom-pages', AdmissionCustomPageController::class)
                ->except('show')
                ->whereNumber('custom_page');
            Route::resource('navigation-items', AdmissionNavigationItemController::class)
                ->except('show')
                ->whereNumber('navigation_item');
            Route::match(['put', 'patch'], 'navigation-items/display-order', [AdmissionNavigationItemController::class, 'displayOrder'])
                ->name('navigation-items.display-order.update');
        });
});

Route::get('/{pathname}', [PageController::class, 'customPage'])
    ->where('pathname', '(.*)?')
    ->name('custom-page');
