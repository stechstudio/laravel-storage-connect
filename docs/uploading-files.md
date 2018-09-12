# Uploading Files

Once you have a connection setup and loaded you can begin uploading files.

```php
$connection->upload('/path/to/source.pdf', 'My File.pdf');
```

## Queued uploads

By default a queue job is created to handle the upload. This avoids locking up your app during a large upload, ensures that any upload failures are retried.

If you really don't want the upload to be queued you can pass false in as a third argument.

```php
$connection->upload('/path/to/source.pdf', 'My File.pdf', false);
```

## Uploading from S3

You can upload files from an S3 bucket by using the `s3://` protocol. This assumes of course you have your AWS credentials setup in the .env file and read access to the bucket where the files are stored.

```php
$connection->upload('s3://bucket-name/source.pdf', 'My File.pdf');
```

# Uploading from URL

You can also upload from a URL.

```php
$connection->upload('https://www.somewebsite.com/source.pdf', 'My File.pdf');
```

If you are using Dropbox this will use the `save_url` method. Dropbox will pull the file directly form the URL.

If you are using Google this package will download the file first from the URL and do a normal upload.