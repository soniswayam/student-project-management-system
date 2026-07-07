<?php

namespace App\Providers;

use App\Models\CollegeSetting;
use App\Models\Role;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
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
        // Keep index key lengths within MySQL limits for utf8mb4.
        Schema::defaultStringLength(191);

        // Render pagination links with Bootstrap 5 markup (the app uses Bootstrap,
        // not Tailwind) so the Next/Previous controls display correctly.
        Paginator::useBootstrapFive();

        $this->registerPermissionGates();
        $this->applyCollegeSettings();
    }

    /**
     * Define a Gate for every permission key so @can('students.delete') works.
     * Super admins bypass all checks.
     */
    private function registerPermissionGates(): void
    {
        Gate::before(fn ($user) => $user->isSuperAdmin() ? true : null);

        foreach (Role::allKeys() as $key) {
            Gate::define($key, fn ($user) => $user->hasPermission($key));
        }
    }

    /**
     * Override config('college') with the DB-backed settings row so every
     * existing config('college') reference reflects the admin-editable values.
     */
    private function applyCollegeSettings(): void
    {
        if (! Schema::hasTable('college_settings')) {
            return;
        }

        if ($setting = CollegeSetting::current()) {
            config(['college' => array_merge(config('college'), $setting->toConfigArray())]);
        }
    }
}
