<?php

namespace Ratno\GenerateEnum;

use Illuminate\Support\ServiceProvider;

class GenerateEnumServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('generate-enum.php'),
            ], 'config');

             $this->commands([
                 \Ratno\GenerateEnum\Console\Commands\GenerateEnum::class,
             ]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'generate-enum');
    }
}
