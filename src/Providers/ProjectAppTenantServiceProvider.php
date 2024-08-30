<?php
namespace Trueandfalse\Essential\Providers;

use Illuminate\Support\ServiceProvider;
use Trueandfalse\Essential\Middleware\AppTenant;

class ProjectAppTenantServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(\Illuminate\Contracts\Http\Kernel $kernel)
    {
        $kernel->pushMiddleware(AppTenant::class);
    }
}
