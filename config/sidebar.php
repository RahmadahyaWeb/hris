<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    [
        'label' => 'Dashboard',
        'icon' => 'home',
        'route' => 'dashboard',
        'permission' => 'dashboard.view',
    ],

    /*
    |--------------------------------------------------------------------------
    | My Activities (Employee Area)
    |--------------------------------------------------------------------------
    */

    [
        'label' => 'My Activities',
        'icon' => 'bolt',
        'children' => [

            [
                'label' => 'My Attendance',
                'icon' => 'map-pin',
                'route' => 'attendances.index',
                'permission' => '',
            ],

            /*
            [
                'label' => 'My Leave',
                'icon' => 'calendar',
                'route' => 'my-leaves.index',
                'permission' => 'leaves.view',
            ],
            */

            /*
            [
                'label' => 'Leave Balance',
                'icon' => 'chart-bar',
                'route' => 'leave-balances.index',
                'permission' => 'leaves.view',
            ],
            */

        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Leave Management
    |--------------------------------------------------------------------------
    */

    [
        'label' => 'Leave Management',
        'icon' => 'calendar',
        'children' => [

            [
                'label' => 'Leave Requests',
                'icon' => 'document',
                'route' => 'leaves.index',
                'permission' => 'leaves.view',
            ],

            [
                'label' => 'Leave Calendar',
                'icon' => 'calendar-days',
                'route' => 'leaves.monitoring.index',
                'permission' => 'leaves.view',
            ],

        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Attendance Management
    |--------------------------------------------------------------------------
    */

    [
        'label' => 'Attendance Management',
        'icon' => 'computer-desktop',
        'children' => [

            [
                'label' => 'Attendance Monitoring',
                'icon' => 'computer-desktop',
                'route' => 'attendances-monitoring.index',
                'permission' => 'attendances_monitoring.view',
            ],

            /*
            [
                'label' => 'Attendance Corrections',
                'icon' => 'pencil-square',
                'route' => 'attendance-corrections.index',
                'permission' => '',
            ],
            */

        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Reports
    |--------------------------------------------------------------------------
    */

    [
        'label' => 'Reports',
        'icon' => 'chart-bar',
        'children' => [

            [
                'label' => 'Attendance Period',
                'icon' => 'document',
                'route' => 'reports.periode.index',
                'permission' => '',
            ],

            /*
            [
                'label' => 'Attendance Report',
                'icon' => 'calendar-days',
                'route' => 'reports.attendance.index',
                'permission' => '',
            ],
            */

            /*
            [
                'label' => 'Leave Report',
                'icon' => 'document-chart-bar',
                'route' => 'reports.leave.index',
                'permission' => '',
            ],
            */

        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Organization
    |--------------------------------------------------------------------------
    */

    [
        'label' => 'Organization',
        'icon' => 'building-office',
        'children' => [

            [
                'label' => 'Branches',
                'icon' => 'building-office-2',
                'route' => 'branches.index',
                'permission' => 'branches.view',
            ],

            [
                'label' => 'Divisions',
                'icon' => 'building-office',
                'route' => 'divisions.index',
                'permission' => 'divisions.view',
            ],

            [
                'label' => 'Positions',
                'icon' => 'briefcase',
                'route' => 'positions.index',
                'permission' => 'positions.view',
            ],

            /*
            [
                'label' => 'Organization Chart',
                'icon' => 'squares-2x2',
                'route' => 'organization-chart.index',
                'permission' => '',
            ],
            */

        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Management
    |--------------------------------------------------------------------------
    */

    [
        'label' => 'User Management',
        'icon' => 'users',
        'children' => [

            [
                'label' => 'Users',
                'icon' => 'users',
                'route' => 'users.index',
                'permission' => 'users.view',
            ],

            [
                'label' => 'User Devices',
                'icon' => 'device-phone-mobile',
                'route' => 'user-devices.index',
                'permission' => 'user_devices.view',
            ],

        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Access Control
    |--------------------------------------------------------------------------
    */

    [
        'label' => 'Access Control',
        'icon' => 'shield',
        'children' => [

            [
                'label' => 'Roles & Permissions',
                'icon' => 'lock-closed',
                'route' => 'roles.index',
                'permission' => 'roles.view',
            ],

        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | System Settings
    |--------------------------------------------------------------------------
    */

    [
        'label' => 'System Settings',
        'icon' => 'cog-8-tooth',
        'children' => [

            [
                'label' => 'Shifts',
                'icon' => 'arrows-up-down',
                'route' => 'shifts.index',
                'permission' => 'shifts.view',
            ],

            [
                'label' => 'Work Calendars',
                'icon' => 'calendar-days',
                'route' => 'work-calendars.index',
                'permission' => 'work_calendars.view',
            ],

            // [
            //     'label' => 'Calendar Setup',
            //     'icon' => 'calendar',
            //     'route' => 'calendars.index',
            //     'permission' => '',
            // ],

            [
                'label' => 'Employee Schedules',
                'icon' => 'clock',
                'route' => 'employee-schedules.index',
                'permission' => 'employee_schedules.view',
            ],

            [
                'label' => 'Attendance Rules',
                'icon' => 'cog-8-tooth',
                'route' => 'attendances-rules.index',
                'permission' => 'attendance_rules.view',
            ],

        ],
    ],

];
