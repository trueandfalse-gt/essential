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
    public function boot(\Illuminate\Contracts\Http\Kernel $kernel): void
    {
        $kernel->pushMiddleware(EssenTenant::class);

        if (app()->runningInConsole()) {
            $this->publishesMigrations([
                __DIR__ . '/../database/migrations/tenants' => database_path('migrations/tenants'),
            ], 'essentenant-migrations');
        }
    }
}
