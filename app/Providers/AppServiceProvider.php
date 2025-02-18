<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\DynamoDbService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(DynamoDbService::class, function () {
            return new DynamoDbService();
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
