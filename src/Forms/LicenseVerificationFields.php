<?php

namespace CodeLone\LaravelWebInstaller\Forms;

use CodeLone\LaravelWebInstaller\Concerns\StepContract;
use CodeLone\LaravelWebInstaller\Rules\LicenseVerificationRule;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard\Step;

class LicenseVerificationFields implements StepContract
{
    public static function form(): array
    {
        return [
            TextInput::make('license_key')
                ->label('License Key')
                ->required()
                ->rules([
                    'required',
                    'string',
                    'min:10',
                    new LicenseVerificationRule()
                ])
                ->placeholder('Enter your license key')
                ->helperText('Please enter the license key you received when purchasing this software'),
                
            TextInput::make('domain')
                ->label('Domain')
                ->required()
                ->default(request()->getHost())
                ->helperText('The domain where this software will be installed')
                ->rules(['required', 'string'])
        ];
    }

    public static function make(): Step
    {
        return Step::make('license-verification')
            ->label('License Verification')
            ->description('Please enter your license key to verify your software license')
            ->schema(self::form())
            ->afterValidation(function ($state) {
                // License data is already stored in session by the LicenseVerificationRule
                // This step ensures the data persists through the installation process
            });
    }
}