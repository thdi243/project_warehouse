<?php

namespace App\Providers;

use App\Models\User;
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
        Gate::define('permission', function (User $user, string $permissionName) {
            return $user->hasPermission($permissionName);
        });

        Gate::define('role', function (User $user, string $roleName) {
            return $user->hasRole($roleName);
        });

        // Super Admin / Admin bypass semua gate
        Gate::before(function (User $user, string $ability) {

            // bypass via permission
            if ($user->hasPermission('super-admin')) {
                return true;
            }

            // bypass via role
            if ($user->hasRole('super-admin')) {
                return true;
            }

            // bypass via flag database
            if ($user->is_admin ?? false) {
                return true;
            }

            return null;
        });
    }
}
