<?php

namespace App\Providers;

use App\Models\Exhibition;
use App\Policies\ExhibitionPolicy;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(TenantContext::class, fn () => new TenantContext());
        $this->app->alias(TenantContext::class, 'tenant.context');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Exhibition::class, ExhibitionPolicy::class);
    }
}
