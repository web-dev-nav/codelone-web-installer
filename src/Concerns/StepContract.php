<?php

namespace CodeHive\LaravelWebInstaller\Concerns;

use Filament\Forms\Components\Wizard\Step;

interface StepContract
{
    /**
     * Get form schema for the step
     */
    public static function form(): array;

    /**
     * Create the wizard step
     */
    public static function make(): Step;
}