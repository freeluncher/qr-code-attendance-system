<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\DashboardRepository;
use App\Services\DashboardService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Dashboard Repository and Service
        $this->app->bind(DashboardRepository::class, DashboardRepository::class);
        $this->app->bind(DashboardService::class, function ($app) {
            return new DashboardService($app->make(DashboardRepository::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
