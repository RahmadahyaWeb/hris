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
        'label' => 'Presence Settings',
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

        ],
    ],

];
