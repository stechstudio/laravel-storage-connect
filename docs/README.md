# Introduction

This is a Laravel package designed to drastically simplify the process of authenticating to a user's cloud storage account and uploading files.

- Handles OAuth flow to authorize a cloud storage account
- Serializes and handles storing/loading of access tokens and connection details
- Queues upload tasks
- Retries upload failures with increasing backoff
- Supports uploading from local filesystem, S3 hosted files (registers a stream wrapper), or any URL
- Automatically disables a connection if storage quota is full, and then re-enables if space is freed up
- Fires events for all activity, and can optionally log activity for you

## Quick example

Let's say you want to connect to the Dropbox account of your currently logged-in user. 

You would create a route that simply does this:

```php
return Auth::user()->dropbox->authorize($redirectUrl);
```

This will redirect the user through the Dropbox oauth process, handle the callback, store the access tokens in the database for your user, and finally redirect back to your desired page.

Now later on when you want to upload a file to this cloud storage:

```php
Auth::user()->dropbox->upload("/path/to/source/file.pdf", "uploaded.pdf");
```

This creates a queued job to upload the file, retry on error, and will dispatch an event when it succeeds or if it ultimately fails.