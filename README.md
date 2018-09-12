# Laravel Storage Connect

This package is designed to drastically simplify the process of authenticating to a user's cloud storage account uploading files.
 
 - Handles OAuth flow to authorize a cloud storage account
 - Serializes and handles storing/loading of connection details
 - Queues upload tasks
 - Retries upload failures with increasing backoff
 - Supports uploading from local filesystem, S3 hosted files (registers a stream wrapper), or any URL
 - Automatically disables a connection if storage quota is full, and then re-enables if space is freed up
 - Fires events for all activity, and can optionally log activity for you

Currently Dropbox and Google Drive are the supported cloud storage backends.

➔ [View full documentation](https://stechstudio.github.io/laravel-storage-connect/)