<?php
Route::prefix(config('storage-connect.path'))->middleware(config('storage-connect.middleware'))->group(function() {

    if(config('storage-connect.authorize_route')) {
        Route::get('authorize/{driver?}', function ($driver) {
            return Auth::user()->getCloudStorage($driver)->authorize(request('redirect'));
        });
    }

    Route::get('callback/{driver}', function($driver) {
        return StorageConnect::finish($driver);
    });
});