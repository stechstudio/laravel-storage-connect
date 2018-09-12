# Eloquent-Managed Connections

The primary way of using this package is through an Eloquent model. This is best when you will be setting up cloud storage connections for users (or organizations) in your database.

## Create database migration

You will need to add a database column for each cloud provider you wish to support. You can name the column(s) whatever you want.

Each column will store a JSON string with all the connection details. So your column type should either be `TEXT` or `JSON` (for PostgreSQL we recommend `JSONB`).

This package is quite happy with `TEXT`, we don't query the JSON directly in the database. However, you may find it beneficial to use a `JSON` type so that you could potentially query it (list users where their Dropbox account is full, for example).

Create a migration for the table where you will store the connections. This will normally be your users table, or possibly your organizations instead.

```php
Schema::table('users', function (Blueprint $table) {
    $table->json('dropbox')->nullable();
    $table->json('google_drive')->nullable();
});
```

## Add model trait and provider map

Edit your Eloquent model class and add the ConnectsToCloudStorage trait, along with a class property that maps cloud providers to your database column names.

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use STS\StorageConnect\Traits\ConnectsToCloudStorage;

class User extends Model {
    use ConnectsToCloudStorage;
    
    /***
     * @var array 
     */
    protected $cloudStorageConnections = [
        'dropbox' => 'dropbox',
        'google' => 'google_drive'
    ];
```

## Pseudo attribute

The trait provides a pseudo attribute for each cloud storage provider using the name of the storage provider followed by `_connection`, so `$user->dropbox_connection->...`, for example.

## Setup a new connection

This package provides a pre-wired route to setup a cloud storage connection for the currently logged in user. This route is available at `/storage-connect/authorize-user/dropbox` by default (change 'dropbox' to any supported provider name).

Of course you can create your own route if need be, particularly if it's not the User that holds the storage connection in the database. To setup a new connection use the authorize method. This will give you a `RedirectResponse` that kicks off the OAuth flow.

Let's say you are storing the connection on the organization. Your route would look like this:

```php
Route::get('/my-endpoint', function() {
    return Auth::user()->organization->dropbox_connection->authorize();
});
```

## Redirect when finished

Both authorization methods above provide a means of specifying the final redirect location once OAuth is complete. For the pre-wired endpoint include a redirect GET parameter:

```
/storage-connect/authorize-user/dropbox?redirect=/dashboard
```

If you are calling the authorize method simply pass your redirect URL as an argument:

```php
return Auth::user()->organization->dropbox_connection->authorize('/dashboard');
```

If you don't provide a redirect URL at all the config `redirect_after_connect` value will be used.

## Load existing connection

You can now interact with your cloud storage connections from the Eloquent model:

```php
User::find(1)->dropbox_connection->upload(...);
```
