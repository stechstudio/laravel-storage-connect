# Setup Google Drive

If you want to use Google Drive as a storage provider option go through these steps to setup Google configure it in your application.

## Create your Google project

 1. Login to your Google account and head over to the Google APIs Console
 2. Create a new project using the dropdown at the top
 3. Make sure your new project is selected at the top, then click on Library on the left menu
 4. Search for and enable the Google+ and Google Drive APIs
 5. Now select Credentials on the left menu and select the "OAuth consent screen" tab at the top
 6. Enter a product name on this page and save, the rest is optional
 7. Now go back to the Credentials tab and select Create credentials -> OAuth client ID
 8. Select Web application as the type, give it a name, and provide the redirect URI for your app

## Redirect URI

On that last step in the above instructions Google asks for your redirect URI. You will need to register the redirect URI for each environment (development, production, etc).

This package uses `/storage-connect/callback/google` as the path for the Google redirect URI.

## Copy your credentials

Copy the Client ID and Client secret and store them as `GOOGLE_ID` and `GOOGLE_SECRET` in your .env file, respectively.
