<?php

return [

    'sidebar' => [

        [
            'heading' => 'Platform',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'icon' => 'home',
                    'route' => 'dashboard',
                    'permission' => null,
                ],
            ],
        ],

        [
            'heading' => 'Access Control',
            'items' => [
                [
                    'label' => 'Roles & Permissions',
                    'icon' => 'shield-check',
                    'route' => 'roles.index',
                    'permission' => 'role.view',
                    'active' => [
                        'roles.*', // wildcard
                    ],
                ],
            ],
        ],

        [
            'heading' => 'User Management',
            'items' => [
                [
                    'label' => 'Users',
                    'icon' => 'users',
                    'route' => 'users.index',
                    'permission' => 'user.view',
                    'active' => [
                        'users.*', // wildcard
                    ],
                ],
            ],
        ],

        [
            'heading' => 'Organization',
            'items' => [
                [
                    'label' => 'Branches',
                    'icon' => 'building-office-2',
                    'route' => 'branches.index',
                    'permission' => 'branch.view',
                    'active' => [
                        'branches.*', // wildcard
                    ],
                ],
                [
                    'label' => 'Departments',
                    'icon' => 'building-office',
                    'route' => 'departments.index',
                    'permission' => 'department.view',
                    'active' => [
                        'departments.*', // wildcard
                    ],
                ],
                [
                    'label' => 'Positions',
                    'icon' => 'briefcase',
                    'route' => 'positions.index',
                    'permission' => 'position.view',
                    'active' => [
                        'positions.*', // wildcard
                    ],
                ],
                [
                    'label' => 'Teams',
                    'icon' => 'rectangle-group',
                    'route' => 'teams.index',
                    'permission' => 'team.view',
                    'active' => [
                        'teams.*', // wildcard
                    ],
                ],
                [
                    'label' => 'Employee',
                    'icon' => 'user-plus',
                    'route' => 'employee-assignments.index',
                    'permission' => 'employee-assignment.view',
                    'active' => [
                        'employee-assignments.*', // wildcard
                    ],
                ],
            ],
        ],

        [
            'heading' => 'System Settings',
            'items' => [
                [
                    'label' => 'Shifts',
                    'icon' => 'arrows-up-down',
                    'route' => 'shifts.index',
                    'permission' => 'shift.view',
                    'active' => [
                        'shifts.*', // wildcard
                    ],
                ],

                [
                    'label' => 'Work Schedules',
                    'icon' => 'calendar-days',
                    'route' => 'work-schedules.index',
                    'permission' => 'shift.view',
                    'active' => [
                        'work-schedules.*', // wildcard
                    ],
                ],

                [
                    'label' => 'Employee Schedules',
                    'icon' => 'calendar-days',
                    'route' => 'employee-schedules.index',
                    'permission' => 'employee-schedule.view',
                    'active' => [
                        'employee-schedules.*', // wildcard
                    ],
                ],

                [
                    'label' => 'Holidays',
                    'icon' => 'calendar-days',
                    'route' => 'holidays.index',
                    'permission' => 'holiday.view',
                    'active' => [
                        'holidays.*', // wildcard
                    ],
                ],
            ],
        ],

    ],

];
