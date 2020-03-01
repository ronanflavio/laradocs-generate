<?php

namespace Ronanflavio\LaradocsGenerate;

use Illuminate\Support\ServiceProvider;

class LaradocsGenerateServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        if (! $this->app->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__.'/../config/docs.php', 'docs');
        }
//        $this->app->make('ronanflavio\laradocs-generate\LaradocsGenerateController');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resource/views', 'laradocs-generate');

        $this->publishes([
            __DIR__.'/../config/docs.php' => config_path('docs.php'),
        ], 'laradocs-config');

        $this->publishes([
            __DIR__.'/../resource/views' => resource_path('views'),
        ], 'laradocs-views');

        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\DocsGenerate::class
            ]);
        }
    }
}
