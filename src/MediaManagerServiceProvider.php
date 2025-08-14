<?php

namespace Nesazit\MediaManager;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use Nesazit\MediaManager\Http\Livewire\MediaManager;
use Nesazit\MediaManager\Http\Livewire\MediaPicker;
use Nesazit\MediaManager\Services\MediaManagerService;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MediaManagerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('media-manager')
            ->hasConfigFile()
            ->hasMigrations()
            ->hasViews()
            ->hasAssets();
    }

    public function packageBooted(): void
    {
        // Register service
        $this->app->singleton('media-manager', function ($app) {
            return new MediaManagerService();
        });

        FilamentAsset::register([
            Css::make('media-manager', __DIR__ . '/../resources/dist/media-manager.css')->loadedOnRequest(),
            Js::make('media-manager', __DIR__ . '/../resources/dist/media-manager.js')/* ->loadedOnRequest() */,
        ], 'nesazit/media-manager');

        // Register Livewire components
        Livewire::component('media-manager', MediaManager::class);
        Livewire::component('media-picker', MediaPicker::class);
    }
}
