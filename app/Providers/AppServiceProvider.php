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
    public function register(): void
    {
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Paginator::useBootstrapFive();

        $this->registerPermissionGates();
        $this->applyCollegeSettings();
    }

    private function registerPermissionGates(): void
    {
        Gate::before(fn ($user) => $user->isSuperAdmin() ? true : null);

        foreach (Role::allKeys() as $key) {
            Gate::define($key, fn ($user) => $user->hasPermission($key));
        }
    }

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
