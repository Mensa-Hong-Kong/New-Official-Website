<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! in_array(config('app.env', 'production'), ['production', 'state'])) {
            Gate::before(
                function (User $user, string $ability) {
                    return $user->hasRole('Super Administrator') ? true : null;
                }
            );
        }
    }
}
