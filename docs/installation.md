# Installation

## Install composer package

```
composer require stechstudio/laravel-storage-connect
```

## Add service provider

If you are using a version of Laravel earlier than 5.5, you will need to manually add the service provider and facade to your config/app.php file:

```php
'providers' => [
    ...
    STS\StorageConnect\StorageConnectServiceProvider::class,
]
'aliases' => [
    ...
    'StorageConnect' => STS\StorageConnect\StorageConnectFacade::class,
]
```

## Run migrations

This package creates a new database table for `cloud_storages`. You will need to run migrations to set this up.

```php
php artisan migrate
```

## Configure storage providers

Currently Dropbox and Google Drive are the supported cloud storage backends.

To use either (or both) you need to ensure your config/services.php file is setup properly:

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

## Add Eloquent model trait

Now go edit the Eloquent model where you want to manage the cloud storage connections. Typically this would be your `User` model, but it might instead be an `Organization` or perhaps `Account`, etc.

Add the `ConnectsToCloudStorage` trait to any Eloquent model, for example:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use STS\StorageConnect\Traits\ConnectsToCloudStorage;

class User extends Model {
    use ConnectsToCloudStorage;
    
    ...
```

You are now ready to setup cloud storage connections through the relationship provided on your model. The trait sets up relationships for each supported cloud storage provider, namely `dropbox` and `google`.