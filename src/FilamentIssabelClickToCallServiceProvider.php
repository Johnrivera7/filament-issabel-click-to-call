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
}
