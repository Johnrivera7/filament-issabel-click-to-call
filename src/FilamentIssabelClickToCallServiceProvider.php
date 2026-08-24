<?php

declare(strict_types=1);

namespace JohnRivera7\FilamentIssabelClickToCall;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentIssabelClickToCallServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-issabel-click-to-call';

    public static string $viewNamespace = 'filament-issabel-click-to-call';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations();
    }

    public function packageBooted(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../resources/issabel/filament-click-to-call.conf' => resource_path('issabel/filament-click-to-call.conf'),
                __DIR__.'/../docs/ISSABEL_VISOR_DESTINO.md' => base_path('docs/ISSABEL_VISOR_DESTINO.md'),
            ], 'filament-issabel-click-to-call-dialplan');
        }
    }
}
