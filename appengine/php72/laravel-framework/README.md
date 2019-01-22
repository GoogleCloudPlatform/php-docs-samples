# Laravel Framework on App Engine Standard for PHP 7.2

[Laravel][laravel] is an open source web framework for PHP developers that encourages the use of the model-view-controller (MVC) pattern.

You can check out [PHP on Google Cloud Platform][php-gcp] (GCP) to get an
overview of PHP and learn ways to run PHP apps on GCP.

## Prerequisites

1. Create a project in the [Google Cloud Platform Console](https://console.cloud.google.com/project).
2. Enable billing for your project.
3. Install the [Google Cloud SDK][cloud_sdk].
4. Authenticate with the Cloud SDK - `gcloud auth application-default login`

## Prepare

Follow the official documentation for [installing Laravel][laravel-install] from
laravel.com. This version was tested to work with `laravel/laravel-framework:^5.6`.

## Run

1. Run the app with the following command:

        php artisan serve

2. Visit [http://localhost:8000](http://localhost:8000) to see the Laravel
   Welcome page.

## Deploy

1. Create an `app.yaml` file with the following contents:

        runtime: php72

        env_variables:
          # Put production environment variables here.
          APP_LOG: errorlog
          APP_KEY: YOUR_APP_KEY
          APP_STORAGE: /tmp

2. Copy the [`bootstrap/app.php`](bootstrap/app.php) and
  [`config/view.php`](config/view.php) files included in this sample into the
  corresponding directories of your Laravel application. These two files ensure
  your Laravel application writes to `/tmp` for caching in production.

  > If you are using an existing Laravel application, just copy the
  `google-app-engine-deployment` blocks from these files.

3. Replace `YOUR_APP_KEY` in `app.yaml` with an application key you generate
  with the following command:

        php artisan key:generate --show

    If you're on Linux or macOS, the following command will automatically
    update your `app.yaml`:

        sed -i '' "s#YOUR_APP_KEY#$(php artisan key:generate --show --no-ansi)#" app.yaml

4. Run the following command to deploy your app:

        gcloud app deploy

5. Visit `http://YOUR_PROJECT_ID.appspot.com` to see the Laravel welcome page. Replace `YOUR_PROJECT_ID`
   with the ID of your GCP project.

    ![Laravel welcome page][laravel-welcome]

## Set up Database Sessions with Cloud SQL

**Note**: This section only works with Laravel 5.4.16 and above. To use earlier versions of
Laravel, you need to manually add the `DB_SOCKET` value to
`config/database.php` (see [#4178](https://github.com/laravel/laravel/pull/4179/files))

1. Follow the instructions to set up a
   [Google Cloud SQL Second Generation instance for MySQL][cloudsql-create].
   Keep track of your instance name and password, as they
   will be used below.

2. Follow the instructions to
   [install the Cloud SQL proxy client on your local machine][cloudsql-install].
   The Cloud SQL proxy is used to connect to your Cloud SQL instance when running
   locally.

   * Use the [Google Cloud SDK][cloud_sdk] from the command line to run the following command. Copy the `connectionName` value for the next step. Replace `YOUR_INSTANCE_NAME` with the name of your instance:

            gcloud sql instances describe YOUR_INSTANCE_NAME | grep connectionName

    * Start the Cloud SQL proxy and replace `YOUR_CONNECTION_NAME` with the connection name you retrieved in the previous step.

            cloud_sql_proxy -instances=YOUR_CONNECTION_NAME=tcp:3306

    * Use `gcloud` to create a database for the application.

            gcloud sql databases create laravel --instance=YOUR_INSTANCE_NAME
            
    * The default service account needs to have the `Cloud SQL Client` role. The Cloud SQL Admin API must also be enabled under `APIs and Services` in order to use the Cloud SQL Proxy Client.

3. Run the database migrations for Laravel. This can be done locally by setting
  your parameters in `.env` or by passing them in as environment variables. Be
  sure to replace `YOUR_DB_PASSWORD` below with the root password you
  configured:

        # create a migration for the session table
        php artisan session:table
        export DB_DATABASE=laravel DB_USERNAME=root DB_PASSWORD=YOUR_DB_PASSWORD
        php artisan migrate --force

4. Modify your `app.yaml` file with contents from [`app-dbsessions.yaml`](app-dbsessions.yaml):

        runtime: php72

        env_variables:
          # Put production environment variables here.
          APP_LOG: errorlog
          APP_KEY: YOUR_APP_KEY
          APP_STORAGE: /tmp
          CACHE_DRIVER: database
          SESSION_DRIVER: database
          ## Set these environment variables according to your CloudSQL configuration.
          DB_DATABASE: laravel
          DB_USERNAME: root
          DB_PASSWORD: YOUR_DB_PASSWORD
          DB_SOCKET: "/cloudsql/YOUR_CONNECTION_NAME"

5. Replace each instance of `YOUR_DB_PASSWORD` and `YOUR_CONNECTION_NAME`
   with the values you created for your Cloud SQL instance above.

## Set up Stackdriver Logging and Error Reporting

Before we begin, install both of the Google Cloud client libraries for Stackdriver
Logging and Error Reporting:

        composer require google/cloud-logging google/cloud-error-reporting

### Stackdriver Logging

You can write logs to Stackdriver Logging from PHP applications by using the Stackdriver Logging library for PHP directly.

1. First, create a custom logger in `app/Logging/CreateCustomLogger.php`:
    ```php
    namespace App\Logging;

    use Google\Cloud\Logging\LoggingClient;
    use Monolog\Handler\PsrHandler;
    use Monolog\Logger;

    class CreateCustomLogger
    {
        /**
         * Create a custom Monolog instance.
         *
         * @param  array  $config
         * @return \Monolog\Logger
         */
        public function __invoke(array $config)
        {
            $logName = isset($config['logName']) ? $config['logName'] : 'app';
            $psrLogger = LoggingClient::psrBatchLogger($logName);
            $handler = new PsrHandler($psrLogger);
            $logger = new Logger($logName, [$handler]);
            return $logger;
        }
    }
    ```

2. Next, you'll need to add our new custom logger to `config/logging.php`:

    ```php
    'channels' => [

        // Add the following lines to integrate with Stackdriver:
        'stackdriver' => [
            'driver' => 'custom',
            'via' => App\Logging\CreateCustomLogger::class,
            'level' => 'debug',
        ],
    ```

3. Now you can log to Stackdriver logging anywhere in your application!

    ```php
    Log::info("Hello Stackdriver! This will show up as log level INFO!");
    ```

### Stackdriver Error Reporting

You can send error reports to Stackdriver Error Reporting from PHP applications by using the
[Stackdriver Error Reporting library for PHP](http://googleapis.github.io/google-cloud-php/#/docs/cloud-error-reporting/v0.12.3/errorreporting/readme).


1. Add the following `use` statement at the beginning of the file `app/Exceptions/Handler.php`:
    ```php
    use Google\Cloud\ErrorReporting\Bootstrap;
    ```

2. Edit the `report` function in the same file (`app/Exceptions/Handler.php`) as follows:
    ```php
    public function report(Exception $exception)
    {
        if (isset($_SERVER['GAE_SERVICE'])) {
            Bootstrap::init();
            Bootstrap::exceptionHandler($exception);
        } else {
            parent::report($exception);
        }
    }
    ```

3. Now any PHP Exception will be logged to Stackdriver Error Reporting!
    ```php
    throw new \Exception('PHEW! We will see this in Stackdriver Error Reporting!');
    ```

[php-gcp]: https://cloud.google.com/php
[laravel]: http://laravel.com
[laravel-install]: https://laravel.com/docs/5.4/installation
[laravel-welcome]: https://storage.googleapis.com/gcp-community/tutorials/run-laravel-on-appengine-flexible/welcome-page.png
[cloud_sdk]: https://cloud.google.com/sdk/
[cloudsql-create]: https://cloud.google.com/sql/docs/mysql/create-instance
[cloudsql-install]: https://cloud.google.com/sql/docs/mysql/connect-external-app#install
