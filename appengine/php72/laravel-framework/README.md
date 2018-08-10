Laravel on App Engine for PHP 7.2
=================================

[Laravel][laravel] is an open source web framework for PHP developers that encourages the use of the model-view-controller (MVC) pattern.

You can check out [PHP on Google Cloud Platform][php-gcp] (GCP) to get an
overview of PHP and learn ways to run PHP apps on GCP.

## Prerequisites

1. Create a project in the [Google Cloud Platform Console](https://console.cloud.google.com/project).
1. Enable billing for your project.
1. Install the [Google Cloud SDK][cloud_sdk].

## Prepare

Follow the official documentation for [installing Laravel][laravel-install] from
laravel.com. This version was tested to work with `laravel/laravel-framework:^5.6`.

## Run

1. Run the app with the following command:

        php artisan serve

1. Visit [http://localhost:8000](http://localhost:8000) to see the Laravel
   Welcome page.

## Deploy

1. Create an `app.yaml` file with the following contents:

        runtime: php72

        env_variables:
          # Put production environment variables here.
          APP_LOG: errorlog
          APP_KEY: YOUR_APP_KEY
          APP_STORAGE: /tmp

1. Copy the [`bootstrap/app.php`](bootstrap/app.php) and
  [`config/view.php`](config/view.php) files included in this sample into the
  corresponding directories of your Laravel application.

1. Replace `YOUR_APP_KEY` in `app.yaml` with an application key you generate
  with the following command:

        php artisan key:generate --show

    If you're on Linux or macOS, the following command will automatically
    update your `app.yaml`:

        sed -i '' "s#YOUR_APP_KEY#$(php artisan key:generate --show --no-ansi)#" app.yaml

1. Run the following command to deploy your app:

        gcloud app deploy

1. Visit `http://YOUR_PROJECT_ID.appspot.com` to see the Laravel welcome page. Replace `YOUR_PROJECT_ID`
   with the ID of your GCP project.

    ![Laravel welcome page][laravel-welcome]

## Set up Database Sessions

**Note**: This section only works with Laravel 5.4.16. To use earlier versions of
Laravel, you need to manually add the `DB_SOCKET` value to
`config/database.php` (see [#4178](https://github.com/laravel/laravel/pull/4179/files))

1. Follow the instructions to set up a
   [Google Cloud SQL Second Generation instance for MySQL][cloudsql-create].

1. Follow the instructions to
   [install the Cloud SQL proxy client on your local machine][cloudsql-install].
   The Cloud SQL proxy is used to connect to your Cloud SQL instance when running
   locally.

1. Use the [Cloud SDK][cloud_sdk] from the command line to run the following command. Copy
   the `connectionName` value for the next step. Replace `YOUR_INSTANCE_NAME` with the name
   of your instance:

        gcloud sql instances describe YOUR_INSTANCE_NAME

1. Start the Cloud SQL proxy and replace `YOUR_INSTANCE_CONNECTION_NAME` with
   the connection name you retrieved in the previous step:

        cloud_sql_proxy -instances=YOUR_INSTANCE_CONNECTION_NAME=tcp:3306

1. Use `gcloud`, or a MySQL client, to connect to your instance and create a
  database for the application.

        gcloud sql databases create laravel --instance=YOUR_INSTANCE_NAME

1. Run the database migrations for Laravel. This can be done locally by setting
  your parameters in `.env` or by passing them in as environment variables. Be
  sure to replace `YOUR_DB_PASSWORD` below with the root password you
  configured:

        # create a migration for the session table
        php artisan session:table
        DB_DATABASE=laravel DB_USERNAME=root DB_PASSWORD=YOUR_DB_PASSWORD php artisan migrate --force

1. Modify your `app.yaml` file with the following contents:

        runtime: php72

        env_variables:
          # Put production environment variables here.
          APP_LOG: errorlog
          APP_KEY: YOUR_APP_KEY
          APP_STORAGE: /tmp
          CACHE_DRIVER: database
          SESSION_DRIVER: database
          ## Set these environment variables according to your CloudSQL configuration.
          DB_HOST: localhost
          DB_DATABASE: laravel
          DB_USERNAME: root
          DB_PASSWORD: YOUR_DB_PASSWORD
          DB_SOCKET: "/cloudsql/YOUR_CLOUDSQL_CONNECTION_NAME"

1. Replace each instance of `YOUR_DB_PASSWORD` and `YOUR_CLOUDSQL_CONNECTION_NAME`
   with the values you created for your Cloud SQL instance above.

[php-gcp]: https://cloud.google.com/php
[laravel]: http://laravel.com
[laravel-install]: https://laravel.com/docs/5.4/installation
[laravel-welcome]: https://storage.googleapis.com/gcp-community/tutorials/run-laravel-on-appengine-flexible/welcome-page.png
[cloud_sdk]: https://cloud.google.com/sdk/
[cloudsql-create]: https://cloud.google.com/sql/docs/mysql/create-instance
[cloudsql-install]: https://cloud.google.com/sql/docs/mysql/connect-external-app#install

