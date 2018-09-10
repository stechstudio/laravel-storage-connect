# Laravel Storage Connect

This package drastically simplifies the process of authenticating to a user's cloud storage account and sending files to that cloud storage.

## Quick example

Let's say you want to connect to the Dropbox account of your currently logged-in user. In a controller method you would simply call:

```php
return Auth::user()->dropbox_connection->setup($redirectUrl);
```

This will redirect the user through the Dropbox oauth process, handle the callback, store the access tokens in the database for your user, and finally redirect back to your desired page.

Now later on when you want to upload a file to this cloud storage:

```php
Auth::user()->dropbox_connection->upload("/path/to/source/file.pdf", "uploaded.pdf");
```

This creates a _queued_ job to upload the file, retry on error, and will dispatch an event when it succeeds or if it ultimately fails.

## Cloud storage providers

Currently Dropbox and Google Drive are the supported cloud storage backends.

