<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Repositories\DashboardRepository;
use App\Services\DashboardService;
use App\Services\GeocodingService;
use App\Services\TelegramService;
use App\Events\AttendanceRecorded;
use App\Listeners\SendTelegramNotification;

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

        // Register Geocoding Service
        $this->app->singleton(GeocodingService::class, function ($app) {
            return new GeocodingService();
        });

        // Register Telegram Service
        $this->app->singleton(TelegramService::class, function ($app) {
            return new TelegramService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register event listeners
        Event::listen(
            AttendanceRecorded::class,
            SendTelegramNotification::class,
        );
    }
}
