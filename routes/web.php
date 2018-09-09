<?php
Route::prefix(config('storage-connect.route'))->middleware(config('storage-connect.middleware'))->group(function() {
    Route::get('authorize/{driver?}', function($driver) {
        return StorageConnect::driver($driver)->redirect();
    });

    Route::get('callback/{driver}', function($driver) {
        return StorageConnect::driver($driver)->finish();
    });

    Route::get('test/{driver}', function($driver) {
        dd(StorageConnect::driver($driver)->service());
    });
});