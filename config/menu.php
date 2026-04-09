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
            ],
        ],

    ],

];
