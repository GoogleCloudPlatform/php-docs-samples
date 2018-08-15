# App Engine for PHP 7.2 Error Reporting samples

This app demonstrates how to report errors on App Engine for PHP 7.2 and shows how
different PHP error types are handled.

To set up **error reporting** in your App Engine PHP 7.2 application, simply follow
these two steps:

1. Install the Google Cloud Error Reporting client library
   ```sh
   composer require google/cloud-error-reporting
   ```
2. Create a [`php.ini`](php.ini) file in the root of your project and set
   `auto_prepend_file` to the following:
    ```ini
    ; in php.ini
    auto_prepend_file=/srv/vendor/google/cloud-error-reporting/src/prepend.php
    ```

The [`prepend.php`][prepend] file will be executed prior to each request, which
registers the client library's error handler.

[prepend]: https://github.com/GoogleCloudPlatform/google-cloud-php-errorreporting/blob/master/src/prepend.php

## Setup

- Install [`composer`](https://getcomposer.org)
- Install dependencies by running:

    ```sh
    composer install
    ```

- Install the [Google Cloud SDK](https://developers.google.com/cloud/sdk/).

## Deploy

### Run Locally

You can run the sample locally using PHP's build-in web server:

```
# export environemnt variables locally which are set by app engine when deployed
export GOOGLE_CLOUD_PROJECT=YOUR_PROJECT_ID
export GAE_SERVICE=local
export GAE_VERSION=testing

# Run PHP's built-in web server
php -S localhost:8000
```

Browse to `localhost:8000` to see a list of examples to execute.

### Deploy with gcloud

Deploy the samples by doing the following:

```
gcloud config set project YOUR_PROJECT_ID
gcloud app deploy
gcloud app browse
```

The last command will open `https://{YOUR_PROJECT_ID}.appspot.com/`
in your browser. Browse to `/` to see a list of examples to execute.
