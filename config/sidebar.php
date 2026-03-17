<?php

return [

    [
        'label' => 'Dashboard',
        'icon' => 'home',
        'route' => 'dashboard',
        'permission' => 'dashboard.view',
    ],

    [
        'label' => 'My Dashboard',
        'icon' => 'home',
        'route' => 'employee.dashboard',
        'permission' => 'employee_dashboard.view',
    ],

    [
        'label' => 'My Activities',
        'icon' => 'bolt',
        'children' => [

            [
                'label' => 'My Attendance',
                'icon' => 'document',
                'route' => 'employee.attendance-history',
                'permission' => 'employee_attendance-history.view',
            ],

            [
                'label' => 'Check In / Out',
                'icon' => 'map-pin',
                'route' => 'employee.attendances.index',
                'permission' => 'employee_attendances.view',
            ],

        ],
    ],

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
                'route' => 'leaves.calendar.index',
                'permission' => 'leaves.view',
            ],

        ],
    ],

    [
        'label' => 'Attendance Management',
        'icon' => 'computer-desktop',
        'children' => [

            [
                'label' => 'Attendance Monitoring',
                'icon' => 'computer-desktop',
                'route' => 'attendance.monitoring.index',
                'permission' => 'attendances_monitoring.view',
            ],

        ],
    ],

    [
        'label' => 'Reports',
        'icon' => 'chart-bar',
        'children' => [

            [
                'label' => 'Attendance Report',
                'icon' => 'document',
                'route' => 'reports.attendance-report.index',
                'permission' => 'attendance_report.view',
            ],

        ],
    ],

    [
        'label' => 'Organization',
        'icon' => 'building-office',
        'children' => [

            [
                'label' => 'Branches',
                'icon' => 'building-office-2',
                'route' => 'organization.branches.index',
                'permission' => 'branches.view',
            ],

            [
                'label' => 'Divisions',
                'icon' => 'building-office',
                'route' => 'organization.divisions.index',
                'permission' => 'divisions.view',
            ],

            [
                'label' => 'Positions',
                'icon' => 'briefcase',
                'route' => 'organization.positions.index',
                'permission' => 'positions.view',
            ],

        ],
    ],

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
                'route' => 'users.devices.index',
                'permission' => 'user_devices.view',
            ],

        ],
    ],

    [
        'label' => 'Access Control',
        'icon' => 'shield',
        'children' => [

            [
                'label' => 'Roles & Permissions',
                'icon' => 'lock-closed',
                'route' => 'access-control.roles.index',
                'permission' => 'roles.view',
            ],

        ],
    ],

    [
        'label' => 'System Settings',
        'icon' => 'cog-8-tooth',
        'children' => [

            [
                'label' => 'Shifts',
                'icon' => 'arrows-up-down',
                'route' => 'settings.shifts.index',
                'permission' => 'shifts.view',
            ],

            [
                'label' => 'Work Calendars',
                'icon' => 'calendar-days',
                'route' => 'settings.work-calendars.index',
                'permission' => 'work_calendars.view',
            ],

            [
                'label' => 'Employee Schedules',
                'icon' => 'clock',
                'route' => 'settings.employee-schedules.index',
                'permission' => 'employee_schedules.view',
            ],

            [
                'label' => 'Attendance Rules',
                'icon' => 'cog-8-tooth',
                'route' => 'settings.attendance-rules.index',
                'permission' => 'attendance_rules.view',
            ],

        ],
    ],

];
