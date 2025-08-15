<?php

use Illuminate\Support\Facades\Route;
use CodeLone\LaravelWebInstaller\Http\Livewire\Installer;

Route::get('/', function () {
    return redirect()->route('installer');
})->middleware(['web']);

Route::get('installer', Installer::class)->name('installer')
    ->middleware(['web']);

// Success route removed - now using direct redirect with flash message