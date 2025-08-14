<?php

namespace CodeLone\LaravelWebInstaller\Forms;

use CodeLone\LaravelWebInstaller\Concerns\StepContract;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\View;

class FolderPermissionStep implements StepContract
{
    public static function form(): array
    {
        return [
            View::make('laravel-web-installer::components.folder-permissions')
                ->viewData([
                    'permissions' => self::checkPermissions()
                ])
        ];
    }

    public static function make(): Step
    {
        return Step::make('permissions')
            ->label('Folder Permissions')
            ->description('Check folder permissions')
            ->schema(self::form());
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