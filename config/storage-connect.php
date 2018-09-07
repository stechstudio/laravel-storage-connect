<?php
return [
    'default' => env('STORAGE_CONNECT_DEFAULT','dropbox'),

    'route' => 'storage-connect',

    'middleware' => 'web',

    'redirect_after_connect' => '/',

    'drivers' => ['dropbox']
];