<?php

return [
    /**
     * Control if the seeder should create a user per role while seeding the data.
     */
    'create_users' => false,

    /**
     * Control if all the laratrust tables should be truncated before running the seeder.
     */
    'truncate_tables' => true,

    'roles_structure' => [
        'superadmin' => [
            'admin' => 'c,r,u,d',
            'user' => 'c,r,u,d',
            'humanitarian_case' => 'c,r,u,d',
        ],
        'admin' => [
            'admin' => 'c,r,u,d',
            'user' => 'c,r,u,d',
            'humanitarian_case' => 'c,r,u,d',
        ],
        'user' => [],
    ],

    'permissions_map' => [
        'c' => 'create',
        'r' => 'read',
        'u' => 'update',
        'd' => 'delete',
    ],
];
