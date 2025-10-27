<?php

namespace App\Providers;

use App\Services\CompteService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // register services here
        $this->app->singleton(CompteService::class, function ($app) {
            return new CompteService();
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
