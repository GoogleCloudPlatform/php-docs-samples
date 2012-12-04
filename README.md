# Google Cloud Storage PHP Sample Application

## Description
This is a simple web-based example of calling the Google Cloud Storage API in
PHP.

## Prerequisites:
Please make sure that all of the following is installed before trying to run
the sample application.

- PHP 5.2.x or higher [http://www.php.net/]
- PHP Curl extension [http://www.php.net/manual/en/intro.curl.php]
- PHP JSON extension [http://php.net/manual/en/book.json.php]
- The google-api-php-client library checked out locally

## Setup Authentication
NOTE: This README assumes that you have enabled access to the Google Cloud
Engine API via the Google API Console page.

1) Visit https://code.google.com/apis/console/?api=storage to register your
application.
- Click on "API Access" in the left column
- Click the button labeled "Create an OAuth2 client ID..." if you have not
generated any client IDs, "Create another client ID..." if you have, or
use an existing client, if you prefer
- Give your application a name and click "Next"
- Select "Web Application" as the "Application type"
- Click "Create client ID"
- Click "Edit settings..." for your new client ID
- Under the redirect URI, enter the location of your JavaScript application
- Click "Update"

2) Update app.php with the redirect uri, consumer key, secret, and developer
key obtained in step 1.
- Update 'YOUR_CLIENT_ID' with your oauth2 client id.
- Update 'YOUR_CLIENT_SECRET' with your oauth2 client secret.
- Update 'YOUR_REDIRECT_URI' with the fully qualified
redirect URI.
- Update 'YOUR_DEVELOPER_KEY' with your developer key.
- Update 'YOUR_DEFAULT_PROJECT_NAME' with your project name, which
can be found by visiting https://code.google.com/apis/console/,
clicking the 'Overview' tab on the left-hand side of the screen, and
copying the value of the field labeled 'Project ID'.

3) Update app.php with remaining default settings. Search and replace all
strings starting with 'YOUR_DEFAULT_' with their associated values.

## Running the Sample Application
4) Load app.php on your web server, and visit the appropriate website in
your web browser.
