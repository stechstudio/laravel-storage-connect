# Storage API

## Status

### `isConnected(): bool`

Determines if a connection has been established at all.

### `isEnabled(): bool`

Determines whether a connection has been setup, and is currently enabled.

### `isFull(): bool`

Determines if the connection is disabled due to storage quota.

### `isReady(): bool`

Determines whether a connection has been setup, is currently enabled, and has storage space available.

*This is the method you want to rely on to see if you can upload a file.*

### `getUserName(): string`

Returns the registered user's name from the cloud storage account. 

### `getUserEmail(): string`

Returns the registered email from the cloud storage account.

### `percentFull(): float`

How full the user's cloud storage account was, last we checked.

### `checkSpaceUsage(): void`

## Enabling/disabling

If a connection is disabled due to storage quota, it will be re-checked every 60 minutes. Use this method if you know that the user has cleared out files or upgraded their account and want to force a check right away.

### `disable($reason): void`

Use this if you need to manually disable a connection.

 - `$reason` A single-term lowercase key

```php
$connection->disable("feature-not-available");
```

### `enable(): void`

This will forcefully enable a connection that has been disabled for any reason.

### `authorize($redirectUrl = null): RedirectResponse`

Call this method to initiate the OAuth flow, setting up the storage connection.

 - `$redirectUrl` The final URL where your user should be redirected once the OAuth flow is complete

### `upload($source, $destination, $shouldQueue = true, $queueJob = null)`

Uploads a file to the cloud storage.

 - `$source` Full path to source file. Can be on the local filesystem, in an S3 bucket (using the `s3://` protocol) or a URL.
 - `$destination` Relative path where you want it stored in the user's cloud storage account.
 - `$shouldQueue` Whether you want the upload to be queued (defaults to true).
 - `$queueJob` If you are calling this _from_ a queue job, pass the job in for retry/release handling.