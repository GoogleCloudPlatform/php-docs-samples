# Stackdriver Logging on App Engine Standard for PHP 7.2

This application demonstrates how to set up logging on App Engine Standard for
PHP 7.2. It also demonstrates how different log levels are handled.

To set up **logging** in your App Engine PHP 7.2 application, simply follow
these two steps:

1. Install the Google Cloud Logging client library
   ```sh
   composer require google/cloud-logging
   ```
1. Create a [PSR-3][psr3]-compatible logger object
    ```php
    use Google\Cloud\Logging\LoggingClient;
    // Create a PSR-3-Compatible logger
    $logger = LoggingClient::psrBatchLogger('app');
    ```

Now you can happily log anything you'd like, and they will show up on
[console.cloud.google.com/logs](https://console.cloud.google.com/logs):

```php
// Log messages with varying log levels.
$logger->info('This will show up as log level INFO');
$logger->warning('This will show up as log level WARNING');
$logger->error('This will show up as log level ERROR');
```

[psr3]: https://www.php-fig.org/psr/psr-3/

## Setup the sample

- Install [`composer`](https://getcomposer.org)
- Install dependencies by running:
    ```sh
    composer install
    ```
- Install the [Google Cloud SDK](https://developers.google.com/cloud/sdk/).

## Deploy the sample

### Deploy with `gcloud`

Deploy the samples by doing the following:

```
gcloud config set project YOUR_PROJECT_ID
gcloud app deploy
gcloud app browse
```

The last command will open `https://{YOUR_PROJECT_ID}.appspot.com/`
in your browser. Browse to `/` to send in some logs.

### Run Locally

Run the sample locally using PHP's build-in web server:

```
# export environemnt variables locally which are set by App Engine when deployed
export GOOGLE_APPLICATION_CREDENTIALS=/path/to/credentials.json
export GOOGLE_CLOUD_PROJECT=YOUR_PROJECT_ID

# Run PHP's built-in web server
php -S localhost:8000
```

Browse to `localhost:8000` to send in the logs.

> Note: These logs will show up under the `Global` resource since you are not
actually sending these from App Engine.

