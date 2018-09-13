<?php
Route::prefix(config('storage-connect.path'))->middleware(config('storage-connect.middleware'))->group(function() {

    if(config('storage-connect.authorize_routes')) {
        Route::get('authorize-user/{driver?}', function ($provider) {
            return Auth::user()->getCloudConnection($provider)->authorize(request('redirect'));
        });

        Route::get('authorize/{driver?}', function ($provider) {
            return StorageConnect::connection($provider)->authorize(request('redirect'));
        });
    }

    Route::get('callback/{driver}', function($provider) {
        return StorageConnect::connection($provider)->finish();
    });
});