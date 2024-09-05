<?php
namespace Trueandfalse\Essential\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Trueandfalse\Essential\Middleware\EssenAccess;

class EssentialAccessServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->app->make(Router::class)->aliasMiddleware('essen-access', EssenAccess::class);

        if (app()->runningInConsole()) {
            $this->publishesMigrations([
                __DIR__ . '/../database/migrations/auth' => database_path('migrations'),
            ], 'essenauth-migrations');
            $this->publishes([
                __DIR__ . '/../Models/Auth' => app_path('Models/Auth'),
            ], 'essenauth-models');
            $this->publishes([
                __DIR__ . '/../database/seeders' => database_path('seeders'),
            ], 'essenauth-seeders');
        }
    }
}
