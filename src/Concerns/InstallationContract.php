<?php

namespace CodeLone\LaravelWebInstaller\Concerns;

interface InstallationContract
{
    /**
     * Run the installation process
     */
    public function run(array $data): bool;

    /**
     * Get redirect response after installation
     */
    public function redirect();

    /**
     * Clean up after installation
     */
    public function dehydrate(): void;
}