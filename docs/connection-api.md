# Connection API

## Status methods

### `isConnected(): bool`

Determines if a connection has been established at all.

### `isEnabled(): bool`

Determines whether a connection has been setup, and is currently enabled.

### `isDisabled(): bool`

Determines if the connection has been setup, yet disabled for some reason.

### `isFull(): bool`

Determines if the connection is disabled due to storage quota.

### `user(): array`

Returns an array with the `name` and `email` of the user's cloud storage account.

## Storage quota

### `percentFull(): float`

Returns how full the user's cloud storage account is at this moment.

### `checkStorageQuota(): void`

If a connection is disabled due to storage quota, it will be re-checked every 60 minutes. Use this method if you know that the user has cleared out files or upgraded their account and want to force a check right away.

## Manually enabling/disabling

### `disable($message, $reason)`

Use this if you need to manually disable a connection.

 - `$message` A user friendly message explaining why it is being disabled
 - `$reason` A single-term lowercase key

```php
$connection->disable("User downgraded plan, feature not available", "not-in-plan");
```

### `enable()`

This will forcefully enable a connection that has been disabled for any reason.

## Authorization

### `authorize($redirectUrl): RedirectResponse`

Call this method to initiate the OAuth flow, setting up the storage connection.

 - `$redirectUrl` The final URL where your user should be redirected once the OAuth flow is complete
 
## Uploading

### `upload($source, $destination, $queue = true)`

Uploads a file to the cloud storage.

 - `$source` Full path to source file. Can be on the local filesystem, in an S3 bucket (using the `s3://` protocol) or a URL.
 - `$destination` Relative path where you want it stored in the user's cloud storage account.
 - `$queue` Whether you want the upload to be queued (defaults to true)