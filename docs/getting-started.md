# Getting Started

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