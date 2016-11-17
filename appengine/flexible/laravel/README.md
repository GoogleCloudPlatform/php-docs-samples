Laravel on App Engine Flexible Environment
==========================================

## Overview

This guide will help you deploy Laravel on [App Engine Flexible Environment][1]

## Prerequisites

Before setting up Laravel on App Engine, you will need to complete the following:

  1. Create a project in the [Google Cloud console][2]. Note your **Project ID**, as you will need it
     later.

## Install Laravel

1. Use composer to download Laravel and its dependencies
  ```sh
  composer create-project laravel/laravel
  ```

1. cd laravel
1. composer install
1. php artisan key:generate

## Set up the Database

Laravel on App Engine Flexible uses a database for sessions and cache. This is
to allow the cache and session to persist across instances.

1. Follow the instructions to set up a [CloudSQL Second Generation instance][3]

1. Follow the instructions to
  [install the Cloud SQL Proxy client on your local machine][4]. The Cloud SQL
  Proxy is used to connect to your Cloud SQL instance when running locally.

1. Use the Cloud SDK from command-line to run the following command. Copy the
  connectionName value for the next step.
  ```sh
  gcloud beta sql instances describe [YOUR_INSTANCE_NAME]
  ```

1. Start the Cloud SQL Proxy using the connection name from the previous step:
  ```sh
  cloud_sql_proxy -instances=[INSTANCE_CONNECTION_NAME]=tcp:3306
  ```

1. Use the MySQL client or similar program to connect to your instance and
  create a database for the application. When prompted, use the root password
  you configured:
  ```sh
  mysql -h 127.0.0.1 -u root -p -e "CREATE DATABASE laravel;"
  ```

1. Run the database migrations for Laravel. This can be done by setting your
  parameters in `.env` or by passing them in as environemnt variables:
  ```sh
  # create a migration for the session table
  php artisan session:table
  DB_DATABASE=laravel \
    DB_USERNAME=root \
    DB_PASSWORD=supersecretpassword \
    php artisan migrate --force
  ```
1. Update `app.yaml` with the values for your database configuration.

1. Finally, edit `config/database.php` and add a line for "unix_socket" to the
  'mysql' connection configuration:
  ```php
  'mysql' => [
      // ...
      'unix_socket' => env('DB_SOCKET', ''),
  ```


## Copy over App Engine's `app.yaml` File

For your app to deploy on App Engine Flexible, you will need to copy over
`app.yaml`:

```sh
# clone this repo somewhere
git clone https://github.com/GoogleCloudPlatform/php-docs-samples /path/to/php-docs-samples

# copy the file below to the root directory of your Laravel project
cp /path/to/php-docs-samples/appengine/flexible/laravel/app.yaml /path/to/laravel
```

`app.yaml` contains production environemnt variables and App Engine
configuration for your project.

## Add deploy commands to composer

Finally, you need to have scripts run after your application deploys. Add the
following scripts to your project's composer.json:

```json
{
    "scripts": {
        "post-deploy-cmd": [
            "chmod -R 755 bootstrap\/cache",
            "php artisan cache:clear"
        ]
    }
}
```

[1]: https://cloud.google.com/appengine/docs/flexible/
[2]: https://console.cloud.google.com
[3]: https://cloud.google.com/sql/docs/create-instance
[4]: https://cloud.google.com/sql/docs/external#install
