<?php
Route::prefix(config('storage-connect.path'))->middleware(config('storage-connect.middleware'))->group(function() {
    Route::get('authorize/{driver?}', function($driver) {
        return StorageConnect::driver($driver)->authorize(request('redirect'));
    });

    Route::get('connect/{driver?}', function($driver) {
        return Auth::user()->getStorageConnection($driver)->connect("/jobs/public");
    });

    Route::get('callback/{driver}', function($driver) {
        return StorageConnect::driver($driver)->finish();
    });
});