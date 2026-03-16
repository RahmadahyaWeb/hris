<?php

return [

    [
        'label' => 'Dashboard',
        'icon' => 'home',
        'route' => 'dashboard',
        'permission' => 'dashboard.view',
    ],

    [
        'label' => 'Master Organization',
        'icon' => 'shield',
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

        ],
    ],

    [
        'label' => 'Access Control',
        'icon' => 'shield',
        'children' => [

            [
                'label' => 'Roles',
                'icon' => 'lock-closed',
                'route' => 'roles.index',
                'permission' => 'roles.view',
            ],

        ],
    ],

    [
        'label' => 'Users Management',
        'icon' => 'shield',
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

    [
        'label' => 'Attendance Settings',
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
            [
                'label' => 'Calendars',
                'icon' => 'calendar-days',
                'route' => 'calendars.index',
                'permission' => '',
            ],
            [
                'label' => 'Employee Schedules',
                'icon' => 'clock',
                'route' => 'employee-schedules.index',
                'permission' => 'employee_schedules.view',
            ],
            [
                'label' => 'Rules',
                'icon' => 'cog-8-tooth',
                'route' => 'attendances-rules.index',
                'permission' => 'attendance_rules.view',
            ],

        ],
    ],

    [
        'label' => 'Leaves',
        'icon' => 'cog-8-tooth',
        'children' => [

            [
                'label' => 'Leaves',
                'icon' => 'document',
                'route' => 'leaves.index',
                'permission' => 'leaves.view',
            ],
            [
                'label' => 'Leaves Monitoring',
                'icon' => 'computer-desktop',
                'route' => 'leaves.monitoring.index',
                'permission' => 'leaves.view',
            ],

        ],
    ],

    [
        'label' => 'Attendances',
        'icon' => 'cog-8-tooth',
        'children' => [

            [
                'label' => 'Attendances Monitoring',
                'icon' => 'computer-desktop',
                'route' => 'attendances-monitoring.index',
                'permission' => '',
            ],
            [
                'label' => 'Attendances',
                'icon' => 'bolt',
                'route' => 'attendances.index',
                'permission' => '',
            ],

        ],
    ],

    [
        'label' => 'Reports',
        'icon' => 'cog-8-tooth',
        'children' => [

            [
                'label' => 'Period',
                'icon' => 'document',
                'route' => 'reports.periode.index',
                'permission' => '',
            ],

        ],
    ],

];
