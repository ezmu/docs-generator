<?php

namespace Ezmu\DocsGenerator;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Router;
use Illuminate\Contracts\Http\Kernel;

class DocsGeneratorServiceProvider extends ServiceProvider {

    public function boot() {

        $this->loadViewsFrom(__DIR__ . '/resources/views', 'docs-generator');

        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        $this->publishes([
            __DIR__ . '/resources/views' => resource_path('views/vendor/docs-generator'),
                ], 'docs-generator-views');

        $this->publishes([
            __DIR__ . '/../config/docs-generator.php' => config_path('docs-generator.php'),
                ], 'docs-generator-config');
        $this->publishes([
            __DIR__ . '/../public' => public_path('vendor/docs-generator'),
                ], 'public');
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Ezmu\DocsGenerator\Commands\GenerateDocumentation::class,
            ]);
        }
        $kernel = $this->app->make(Kernel::class);
        $kernel->pushMiddleware(\Ezmu\DocsGenerator\Http\Middleware\LogApiRequestsToFile::class);
    }

    /**
     * Register services.
     */
    public function register() {

        if (file_exists(__DIR__ . '/config/docs-generator.php')) {
            $this->mergeConfigFrom(__DIR__ . '/config/docs-generator.php', 'docs-generator');
        }
    }

}
