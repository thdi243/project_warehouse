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

        // Optional: super admin bypass semua permission
        Gate::before(function (User $user, string $ability) {
            // Misal kalau ada kolom is_super_admin atau permission khusus
            if ($user->hasPermission('super-admin') || $user->is_admin ?? false) {
                return true;
            }
        });
    }
}
