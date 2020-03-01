<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Excluded routes
    |--------------------------------------------------------------------------
    |
    | Insert inside the list bellow all routes you want to remove from the
    | documentation. To match routes with partial name, use the asterisk (*).
    |
    | e.g.: my-excluded-routes/*
    |
    */

    'excluded' => [

        /** Default excluded routes */

        '/',
        'docs',
        'oauth/*',

        /** Insert bellow the custom excluded routes */
    ],
];
