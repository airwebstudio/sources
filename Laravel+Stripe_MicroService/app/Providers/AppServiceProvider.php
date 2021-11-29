<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\Requests\Auth\LoginRequest;
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(LoginRequest::class, function ($app) {
            return new LoginRequest();
        });

        $this->app->singleton(StripeClient::class, function() {
            return new StripeClient(config('stripe.secret'));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        
    }
}
