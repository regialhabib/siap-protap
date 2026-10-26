<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
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
        Model::preventLazyLoading(! app()->isProduction());

        Gate::define('admin', fn (User $user): bool => $user->isAdmin());

        Gate::define('popt', fn (User $user): bool => $user->isPopt());

        Gate::define('manage-opt', fn (User $user): bool => $user->isAdmin() || $user->isPopt());

        Gate::define('view-observation-history', fn (User $user): bool => $user->isAdmin() || ($user->isPopt() && $user->uppt_id !== null));
    }
}
