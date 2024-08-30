<?php
namespace Trueandfalse\Essential\Providers;

use Illuminate\Support\ServiceProvider;
use Trueandfalse\Essential\Middleware\EssenTenant;

class EssentialTenantServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->pushMiddleware(EssenTenant::class);

        if (app()->runningInConsole()) {
            $this->publishesMigrations([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'essentenant-migrations');
        }
    }
}
