<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // ACCESS CONTROL
    Route::livewire('roles', 'pages::roles.index')->name('roles.index');

    // OGRANIZATION
    Route::livewire('branches', 'pages::branches.index')->name('branches.index');
    Route::livewire('divisions', 'pages::divisions.index')->name('divisions.index');
    Route::livewire('positions', 'pages::positions.index')->name('positions.index');

    // USER MANAGEMENT
    Route::livewire('users', 'pages::users.index')->name('users.index');
    Route::livewire('user-devices', 'pages::user-devices.index')->name('user-devices.index');

    // PRESENCE
    Route::livewire('shifts', 'pages::shifts.index')->name('shifts.index');

});

require __DIR__.'/settings.php';
