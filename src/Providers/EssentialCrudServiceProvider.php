<?php
namespace Trueandfalse\Essential\Providers;

use Illuminate\Support\ServiceProvider;

class EssentialCrudServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(\Illuminate\Contracts\Http\Kernel $kernel): void
    {
        $this->publishes([
            __DIR__ . '/../resources/lang' => $this->app->langPath('/'),
        ], 'essencrud-lang');

        $this->loadJsonTranslationsFrom(__DIR__ . '/../resources/lang/json');

    }
}
