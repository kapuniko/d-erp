<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Telegram\Provider as TelegramProvider;

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
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Socialite::extend('telegram', function ($app) {
            return Socialite::buildProvider(
                TelegramProvider::class,
                [
                    'client_id' => config('services.telegram.bot'), // Подставляем bot в client_id
                    'client_secret' => config('services.telegram.token'), // Подставляем token в client_secret
                    'redirect' => config('services.telegram.redirect'),
                ]
            );
        });
    }
}
