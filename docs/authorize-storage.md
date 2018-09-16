# Authorize Storage

There are two ways you can authorize and setup new cloud storage connections.

## Pre-wired route

If you are managing cloud storage connections on your `User` model then you may want to just use an authorization route already setup for you.

At the point in your app when you want users to connect their cloud storage account, simply send them to:

```
/storage-connect/authorize/dropbox?redirect=/dashboard
``` 

(You can replace `dropbox` with any supported cloud provider.)

This will take the logged-in user through the OAuth flow, create the cloud storage connection on the `User` model, and finally redirect to `/dashboard` when finished.

### Middleware group

This pre-wired route is set to use your `web` middleware group by default. You can change this with a [configuration setting](configuration.html#route-middleware).

## Create your own route

If you want more control over the route (middleware, etc) or you are putting your storage connections on a different model, you'll want to create your own authorize route.
 
For example, if you are managing cloud storage connections in the `Organization` model, you might do this: 

```php
Route::get('/my-authorize-endpoint', function() {
    return Auth::user()->organization->authorize("/dashboard");
}
```

This will take the logged-in user through the OAuth flow, create the cloud storage connection on the `Organization` model, and finally redirect to `/dashboard` when finished.

## Default redirect

Both authorize options above provide a way to specify the final redirect location. 

If no redirect is provided, the final redirect will be used from your [configuration setting](configuration.html#redirecting-after-oauth).
