<?php

use Illuminate\Support\Facades\Route;
use CodeLone\LaravelWebInstaller\Http\Livewire\Installer;

Route::get('installer', Installer::class)->name('installer')
    ->middleware(['web']);

Route::get('/installer/success', function () {
    // Only show success page if installation is complete
    if (!file_exists(storage_path('installed'))) {
        return redirect()->route('installer');
    }
    return view('laravel-web-installer::success');
})->name('installer.success')->middleware(['web']);