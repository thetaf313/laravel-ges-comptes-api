<?php

namespace App\Providers;

use App\Contracts\EmailServiceInterface;
use App\Contracts\InAppNotificationInterface;
use App\Contracts\SmsServiceInterface;
use App\Services\CompteService;
use App\Services\Email\EmailService;
use App\Services\InAppNotificationService;
use App\Services\Sms\TwilioSmsService;
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
        $this->app->bind(EmailServiceInterface::class, EmailService::class);
        $this->app->bind(SmsServiceInterface::class, TwilioSmsService::class);
        $this->app->bind(InAppNotificationInterface::class, InAppNotificationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
