# Google Auth on App Engine Standard for PHP 7.2

This sample application demonstrates how to [Authenticate Users](https://cloud.google.com/appengine/docs/standard/php7/authenticating-users)
on App Engine Standard.

## Description

This application shows how to authenticate to Google Cloud APIs using two
different methods. This sample uses Storage as an example, but these methods
will work for any Google Cloud API.

## Deploy to App Engine

1.  **Enable APIs** - [Enable the Storage API](https://console.cloud.google.com/flows/enableapi?apiid=storage-api.googleapis.com)
    and create a new project or select an existing project.
1.  **Clone the repo** and cd into this directory
    ```
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd php-docs-samples/appengine/php72/auth
    ```
1.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install --no-dev` (if composer is installed locally)
    or `composer install --no-dev` (if composer is installed globally).
1.  Run `gcloud app deploy` to deploy to App Engine.

## Run Locally

1.  **Download The Credentials** - Click "Go to credentials" after enabling the
    APIs. Click "New Credentials" and select "Service Account Key". Create a new
    service account, use the JSON key type, and select "Create". Once
    downloaded, set the environment variable `GOOGLE_APPLICATION_CREDENTIALS` to
    the path of the JSON key that was downloaded.
1.  Run PHP's built-in web server with the command `php -S localhost:8000` and
    then view the application in your browser at
    [http://localhost:8000](http://localhost:8000).
