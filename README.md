# Laravel Storage Connect

This package drastically simplifies the process of authenticating to a user's cloud storage account and sending files to that cloud storage.

What this does:
 
 - Handles OAuth flow to authorize a cloud storage account
 - Serializes and handles storing/loading of connection details
 - Queues upload tasks
 - Retries upload failures with increasing backoff
 - Supports uploading from local filesystem, S3 hosted files (registers a stream wrapper), or any URL
 - Automatically disables a connection if storage quota is full, and then re-enables if space is freed up
 - Fires events for all activity, and can optionally log activity for you

## Installation and setup

You know the drill...

`composer require stechstudio/laravel-storage-connect`

### Add service provider (older versions of Laravel)
If you are using a version of Laravel earlier than 5.5, you will need to manually add the service provider and facade to your config/app.php file:

```php
'providers' => [
    ...
    STS\StorageConnect\StorageConnectServiceProvider::class,
]
```

```php
'aliases' => [
    ...
    'StorageConnect' => STS\StorageConnect\StorageConnectFacade::class,
]
```

### Configure storage providers

Currently Dropbox and Google Drive are the supported cloud storage backends.

To use either (or both) you need to ensure your `config/services.php` file is setup properly:

```php
'dropbox' => [
    'client_id' => env('DROPBOX_KEY'),
    'client_secret' => env('DROPBOX_SECRET'),
],
'google' => [
    'client_id' => env ('GOOGLE_ID'),
    'client_secret' => env ('GOOGLE_SECRET')
],
```

Then of course setup the appropriate variables in your .env file. 

## Quick example

There are two primary ways to manage storage connections.

### 1. Using connections through eloquent

Let's say you want to connect to the Dropbox account of your currently logged-in user. In a controller method you would simply call:

```php
return Auth::user()->dropbox_connection->connect($redirectUrl);
```

This will redirect the user through the Dropbox oauth process, handle the callback, store the access tokens in the database for your user, and finally redirect back to your desired page.

Now later on when you want to upload a file to this cloud storage:

```php
Auth::user()->dropbox_connection->upload("/path/to/source/file.pdf", "uploaded.pdf");
```

This creates a _queued_ job to upload the file, retry on error, and will dispatch an event when it succeeds or if it ultimately fails.

### 2. Using without Eloquent

If you aren't looking to link the cloud storage connection to a database model (perhaps you are only looking to configure one connection for your whole app) you can also provide your own load/save methods. 

Stick these in your AppServiceProvider `boot` method:

```php
StorageConnect::saveConnectedStorageUsing(function($connection, $driver) {
    Storage::put($driver . '_connection.json', $connection);
});

StorageConnect::loadConnectedStorageUsing(function($driver) {
    return Storage::get($driver . '_connection.json');
});
```

To authorize a new cloud connection you can redirect to the pre-wired route of `/storage-connect/authorize/dropbox`, or you can create our own route and call:

```php
return StorageConnect::driver('dropbox')->authorize($redirectUrl);
```

Once authorized you you can load a connection and upload files:

```php
StorageConnect::load('dropbox')->upload("/path/to/source/file.pdf", "uploaded.pdf");
```