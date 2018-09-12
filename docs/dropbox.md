# Dropbox

Dropbox is the default storage provider and most commonly used.

## Create your Dropbox app

 1. Login to your Dropbox account, head over to the My apps page, and create a new app
 2. Choose the Dropbox API (not the Business API)
 3. Select "App folder" as the access
 4. Give your app a name
 
## Register your redirect URIs

Dropbox requires you to pre-register the exact URI used to redirect back to your app. You will need to register the redirect URI for each environment (development, production, etc).

This package uses `/storage-connect/callback/dropbox` as the path for the Dropbox redirect URI.

## Copy your credentials

Copy the App key and the App secret, store them as `DROPBOX_KEY` and `DROPBOX_SECRET` in your .env file, respectively.