<?php

namespace CodeLone\LaravelWebInstaller;

use Livewire\Livewire;
use CodeLone\LaravelWebInstaller\Http\Livewire\Installer;
use CodeLone\LaravelWebInstaller\Http\Middleware\RedirectIfNotInstalled;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class WebInstallerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        // Register middleware
        $this->app['router']->aliasMiddleware('redirect.if.not.installed', RedirectIfNotInstalled::class);

        $package->name('laravel-web-installer')
            ->hasAssets()
            ->hasViews('laravel-web-installer')
            ->hasConfigFile('installer')
            ->hasRoute('web')
            ->hasTranslations();
    }

    public function packageBooted(): void
    {
        // Register Livewire component after all services are booted
        if (class_exists(Livewire::class)) {
            Livewire::component('advanced-web-installer', Installer::class);
        }

        // Add protection to Filament panels if installed
        if (class_exists(\Filament\Facades\Filament::class)) {
            \Filament\Facades\Filament::serving(function () {
                if (!file_exists(storage_path('installed'))) {
                    redirect()->route('installer')->send();
                }
            });
        }
    }

    public function packageRegistered(): void
    {
        // Register installation manager binding
        $this->app->bind(
            \CodeLone\LaravelWebInstaller\Concerns\InstallationContract::class,
            config('installer.installation_manager', \CodeLone\LaravelWebInstaller\Manager\CustomInstallationManager::class)
        );
    }
}