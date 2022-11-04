<?php

return [
    /**
     * Repository configuration
     */
    'repository' => [
        'cache' => [
            /*
                |--------------------------------------------------------------------------
                | Cache status
                |--------------------------------------------------------------------------
                |
                | This variable determine if cache option is enabled for all repositories
                | that user WithCache trait.
                |
            */
            'active' => env('REPOSITORY_CACHE', true),

            /*
                |--------------------------------------------------------------------------
                | Cache time
                |--------------------------------------------------------------------------
                |
                | Time of cache expiration (in seconds).
                |
            */
            'time'   => 3600,

            /*
                |--------------------------------------------------------------------------
                | Cache guards
                |--------------------------------------------------------------------------
                |
                | Array of auth guards, that will be use by repository to search actual
                | authenticated user.
                |
            */
            'guards' => [
                'web',
                'api',
            ],
        ],
    ],

];
