<?php

namespace CodeLone\LaravelWebInstaller\Forms;

use CodeLone\LaravelWebInstaller\Concerns\StepContract;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\View;

class ServerRequirementFields implements StepContract
{
    public static function form(): array
    {
        return [
            View::make('laravel-web-installer::components.server-requirements')
                ->viewData([
                    'requirements' => self::checkRequirements(),
                    'permissions' => self::checkPermissions()
                ])
        ];
    }

    public static function make(): Step
    {
        return Step::make('requirements')
            ->label('Server Requirements')
            ->description('Check if your server meets the requirements')
            ->schema(self::form());
    }

    private static function checkRequirements(): array
    {
        $requirements = config('installer.requirements', []);
        $results = [];

        // Check PHP version
        $results['php_version'] = [
            'name' => 'PHP Version >= ' . config('installer.core.minPhpVersion', '8.1'),
            'satisfied' => version_compare(PHP_VERSION, config('installer.core.minPhpVersion', '8.1'), '>='),
            'current' => PHP_VERSION
        ];

        // Check PHP extensions
        foreach ($requirements['php'] ?? [] as $extension) {
            $results['extensions'][$extension] = [
                'name' => $extension . ' Extension',
                'satisfied' => extension_loaded($extension)
            ];
        }

        return $results;
    }

    private static function checkPermissions(): array
    {
        $permissions = config('installer.permissions', []);
        $results = [];

        foreach ($permissions as $folder => $permission) {
            $path = base_path($folder);
            $results[$folder] = [
                'name' => $folder,
                'required' => $permission,
                'current' => is_dir($path) ? substr(sprintf('%o', fileperms($path)), -3) : 'N/A',
                'satisfied' => is_dir($path) && is_writable($path)
            ];
        }

        return $results;
    }
}