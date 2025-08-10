<?php

namespace Nesazit\MediaManager;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Nesazit\MediaManager\Http\Livewire\MediaManager;
use Nesazit\MediaManager\Http\Livewire\MediaPicker;

class MediaManagerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'media-manager');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Register Livewire components
        Livewire::component('media-manager', MediaManager::class);
        Livewire::component('media-picker', MediaPicker::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/media-manager.php' => config_path('media-manager.php'),
            ], 'media-manager-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'media-manager-migrations');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/media-manager'),
            ], 'media-manager-views');

            $this->publishes([
                __DIR__ . '/../resources/css' => public_path('vendor/media-manager/css'),
                __DIR__ . '/../resources/js' => public_path('vendor/media-manager/js'),
            ], 'media-manager-assets');
        }

        $this->registerBladeDirectives();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/media-manager.php', 'media-manager');

        $this->app->singleton('media-manager', function () {
            return new \Nesazit\MediaManager\Services\MediaManagerService();
        });
    }

    public function registerBladeDirectives(): self
    {
        Blade::directive('filemanagerStyles', function () {
            $styles = '';

            $styles .= <<<'html'
                        <script src="https://cdn.tailwindcss.com"></script>

                        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
                    html;

            return $styles;
        });

        Blade::directive('filemanagerScripts', function () {
            $scripts = '';

            $scripts .= <<<'html'
                        <script defer src="https://unpkg.com/@alpinejs/ui@3.13.3-beta.1/dist/cdn.min.js"></script>
                    html;

            return $scripts;
        });

        return $this;
    }
}