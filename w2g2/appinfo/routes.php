<?php

return [
    'routes' => [
        // Config
        [
            'name' => 'config#get_color',
            'url' => '/color',
            'verb' => 'GET'
        ],
        [
            'name' => 'config#directory_lock',
            'url' => '/directory-lock',
            'verb' => 'GET'
        ],
        [
            'name' => 'config#update',
            'url' => '/update',
            'verb' => 'POST'
        ],

        // Lock
        [
            'name' => 'lock#index',
            'url' => '/lock',
            'verb' => 'GET'
        ],
        [
            'name' => 'lock#store',
            'url' => '/lock',
            'verb' => 'POST'
        ],
        [
            'name' => 'lock#delete',
            'url' => '/lock',
            'verb' => 'DELETE'
        ],
    ]
];