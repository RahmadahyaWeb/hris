<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::prefix('employee')->name('employee.')->group(function () {

        Route::livewire('dashboard', 'pages::employee.dashboard')
            ->name('dashboard');

        Route::livewire('attendances', 'pages::attendances.index')
            ->name('attendances.index');

        Route::livewire('attendance-history', 'pages::employee.attendance-history')
            ->name('attendance-history');

        Route::livewire('leave', 'pages::employee.leave')
            ->name('leave');

    });

    /*
    |--------------------------------------------------------------------------
    | ORGANIZATION
    |--------------------------------------------------------------------------
    */

    Route::prefix('organization')->name('organization.')->group(function () {

        Route::livewire('branches', 'pages::branches.index')->name('branches.index');
        Route::livewire('divisions', 'pages::divisions.index')->name('divisions.index');
        Route::livewire('positions', 'pages::positions.index')->name('positions.index');

    });

    /*
    |--------------------------------------------------------------------------
    | USER MANAGEMENT
    |--------------------------------------------------------------------------
    */

    Route::prefix('users')->name('users.')->group(function () {

        Route::livewire('/', 'pages::users.index')->name('index');
        Route::livewire('devices', 'pages::user-devices.index')->name('devices.index');

    });

    /*
    |--------------------------------------------------------------------------
    | ACCESS CONTROL
    |--------------------------------------------------------------------------
    */

    Route::prefix('access-control')->name('access-control.')->group(function () {

        Route::livewire('roles', 'pages::roles.index')->name('roles.index');

    });

    /*
    |--------------------------------------------------------------------------
    | ATTENDANCE MANAGEMENT
    |--------------------------------------------------------------------------
    */

    Route::prefix('attendance')->name('attendance.')->group(function () {

        Route::livewire('monitoring', 'pages::attendances.monitoring.index')
            ->name('monitoring.index');

    });

    /*
    |--------------------------------------------------------------------------
    | LEAVE MANAGEMENT
    |--------------------------------------------------------------------------
    */

    Route::prefix('leaves')->name('leaves.')->group(function () {

        Route::livewire('/', 'pages::leaves.index')->name('index');

        Route::livewire('calendar', 'pages::leaves.monitoring.index')
            ->name('calendar.index');

    });

    /*
    |--------------------------------------------------------------------------
    | REPORTS
    |--------------------------------------------------------------------------
    */

    Route::prefix('reports')->name('reports.')->group(function () {

        Route::livewire('attendance-report', 'pages::reports.attendance-report.index')
            ->name('attendance-report.index');

        // future
        // Route::livewire('attendance-detail', 'pages::reports.attendance-detail.index')
        //     ->name('attendance-detail.index');

    });

    /*
    |--------------------------------------------------------------------------
    | SYSTEM SETTINGS
    |--------------------------------------------------------------------------
    */

    Route::prefix('settings')->name('settings.')->group(function () {

        Route::livewire('shifts', 'pages::shifts.index')->name('shifts.index');

        Route::livewire('work-calendars', 'pages::work-calendars.index')
            ->name('work-calendars.index');

        Route::livewire('employee-schedules', 'pages::employee-schedules.index')
            ->name('employee-schedules.index');

        Route::livewire('attendance-rules', 'pages::attendances.rules.index')
            ->name('attendance-rules.index');

    });

});

require __DIR__.'/settings.php';
