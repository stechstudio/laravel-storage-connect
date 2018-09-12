# Events

This package fires events for most all activity throughout the connection and upload process. 

## Connections

### `ConnectionEstablished`

Fired when a user completes the OAuth flow, establishing a new cloud storage connection. 

### `ConnectionDisabled`

Fired when a connection is disabled for any reason, such as storage being full or access token being invalid, or a manual disabling.

### `ConnectionEnabled`

Fired when...oh, you know what, I think you have the hang of this by now.

## Uploads

### `UploadSucceeded`

Fired when we believe to have successfully uploaded a file to a cloud storage account. Note that in some cases, for instance when uploading a URL with Dropbox, this is not a guarantee since the upload is done asynchronously on Dropbox's end.

### `UploadRetrying`

There are a lot of reasons why an upload might fail and be worth retrying (rate limiting, `5XX` errors, etc). By default, the queued upload job will retry a max of five times.

### `UploadFailed`

Fired after the max retries, or if the failure was deemed not worth retrying (access token is invalid, for example), or if the upload was done synchronously in the first place.
