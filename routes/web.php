<?php

use Illuminate\Support\Facades\Route;
use CodeLone\LaravelWebInstaller\Http\Livewire\Installer;

Route::get('installer', Installer::class)->name('installer')
    ->middleware(['web']);

Route::get('/installer/success', function () {
    return view('laravel-web-installer::success');
})->name('installer.success')->middleware(['web', 'redirect.if.not.installed']);