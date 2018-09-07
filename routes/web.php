<?php
Route::prefix(config('storage-connect.route'))->middleware(config('storage-connect.middleware'))->group(function() {
    Route::get('authorize/{driver?}', function($driver = null) {
        return redirect()->to(app('sts.storage-connect')->driver($driver)->getAuthUrl())->withCookie(cookie('foo', 'bar'));
    });

    Route::get('callback/{driver}', function($driver) {
        return redirect()->to(app('sts.storage-connect')->finish($driver));
    });
});