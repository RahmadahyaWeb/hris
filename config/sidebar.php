<?php

return [

    [
        'label' => 'Dashboard',
        'icon' => 'home',
        'route' => 'dashboard',
        'permission' => 'dashboard.view',
    ],

    [
        'label' => 'Access Control',
        'icon' => 'shield',
        'children' => [

            [
                'label' => 'Users',
                'icon' => 'users',
                'route' => 'users.index',
                'permission' => 'users.view',
            ],

            [
                'label' => 'Roles',
                'icon' => 'lock-closed',
                'route' => 'roles.index',
                'permission' => 'roles.view',
            ],

        ],
    ],

];
