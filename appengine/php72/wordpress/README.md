# WordPress on App Engine Standard for PHP 7.2

This is a simple command-line tool for downloading and configuring
WordPress on App Engine Standard for PHP 7.2.
## Common Prerequisites

* Create a new Cloud Project using the [Cloud Console][cloud-console]
* Enable Billing on that project
* Install [Google Cloud SDK][gcloud-sdk]
* [Enable Cloud SQL API][cloud-sql-api-enable]
* Install [Composer][composer]

### Create and configure a Cloud SQL for MySQL instance

> **Note**: In this guide, we use `wordpress` for the instance name and the database
name. We use `root` for the database user name.

1. Create a new Cloud SQL for MySQL Second Generation instance with the following
command:
    ```sh
    $ gcloud sql instances create wordpress \
        --activation-policy=ALWAYS \
        --tier=db-n1-standard-1
    ```
    > **Note**: you can choose `db-f1-micro` or `db-g1-small` instead of
    `db-n1-standard-1` for the Cloud SQL machine type, especially for the
    development or testing purpose. However, those machine types are not
    recommended for production use and are not eligible for Cloud SQL SLA
    coverage. See our [Cloud SQL SLA](https://cloud.google.com/sql/sla)
    for more details.

1. Next, create the database you want your WordPress site to use:
    ```sh
    $ gcloud sql databases create wordpress --instance wordpress
    ```
1. Finally, change the root password for your instance:
    ```sh
    $ gcloud sql users set-password root \
        --host=% \
        --instance wordpress \
        --password=YOUR_INSTANCE_ROOT_PASSWORD # Don't use this password!
    ```

## Create or Update a WordPress project for App Engine

The `wordpress.php` command provides a convenient way for you to to either create
a new WordPress project or add the required configuration to an existing one.

### Setup

1. Download this repository and `cd` into the `appengine/php72/wordpress` directory
    ```sh
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples.git
    $ cd php-docs-samples/appengine/php72/wordpress
    ```
1. Install the dependencies in this directory using composer
    ```sh
    $ composer install
    ```
    > **Note** If you receive an error about extensions, install `phar` and `zip` PHP
    extensions and retry.

### Create a new WordPress Project

To download WordPress and set it up for Google Cloud, run the `create` command:

```sh
$ php wordpress.php create
```

The command asks you several questions, please answer them. Then you'll have a
new WordPress project. By default it will create `my-wordpress-project` in the
current directory.

> **Note**: To determine the region your database is in, run `gcloud sql instances describe wordpress`

### Update an existing WordPress Project

If you are migrating an existing project to Google Cloud, you can use the
`update` command:

```sh
$ php wordpress.php update path/to/your-wordpress-site
```

The command asks you several questions, please answer them. This will copy the
files in the [`files`](files/) directory and write the proper configuration.
Then your WordPress project will be ready to deploy to Google Cloud!

## Deploy to Google Cloud

`cd` into the root of your WordPress project. 

```sh
$ cd my-wordpress-project
```

Run the following command to deploy your project to App Engine:

```sh
$ gcloud app deploy app.yaml cron.yaml
```

Now you can access your site, and continue the installation step! The URL is
https://PROJECT_ID.appspot.com/

### Enable the Google Cloud Storage plugin

To use the [Google Cloud Storage plugin][gcs-plugin] for media uploads, follow
these steps.

1. Configure the App Engine default GCS bucket for later use. The default App
   Engine bucket is named YOUR_PROJECT_ID.appspot.com. Change the default Access
   Control List (ACL) of that bucket as follows:
    ```
    $ gsutil defacl ch -u AllUsers:R gs://YOUR_PROJECT_ID.appspot.com
    ```
1. Go to the Dashboard at https://PROJECT_ID.appspot.com/wp-admin. On the
   Plugins page, activate the `Google Cloud Storage plugin`.
1. In the plugins Settings page, set your Bucket name to the bucket you
   configured in Step 1.

After activating the plugin, try uploading a media object in a new post
and confirm the image is uploaded to the GCS bucket by visiting the
[Google Cloud console's Storage page][cloud-storage-console].

## Local Development

To access this MySQL instance, use Cloud SQL Proxy. [Download][cloud-sql-proxy-download]
it to your local computer and make it executable.

Go to the [the Credentials section][credentials-section] of your project in the
Console. Click 'Create credentials' and then click 'Service account key.' For
the Service account, select 'App Engine app default service account.' Then
click 'Create' to create and download the JSON service account key to your
local machine. Save it to a safe place.

Run the proxy by the following command:

```sh
$ cloud_sql_proxy \
    -dir /cloudsql \
    -instances=YOUR_PROJECT_ID:us-central1:wordpress \
    -credential_file=/path/to/YOUR_SERVICE_ACCOUNT_JSON_FILE.json
```

Now you can access the Cloud SQL instance with the MySQL client in a separate
command line tab.

```
$ mysql --socket /cloudsql/YOUR_PROJECT_ID:us-central1:wordpress -u root -p
mysql> use database wordpress;
mysql> show tables;
mysql> exit
```

## Various Workflows

### Install and Update WordPress, Plugins, and Themes

Because the `wp-content` directory on the server is read-only, you have
to perform all code updates locally. Run WordPress locally and update the
plugins and themes in the local Dashboard, deploy the code to production, then
activate them in the production Dashboard. You can also use the `wp-cli` utility
as follows (be sure to keep the Cloud SQL proxy running):

```
# Install the wp-cli utility
$ composer require wp-cli/wp-cli-bundle
# Now you can run the "wp" command to update Wordpress itself
$ vendor/bin/wp core update --path=wordpress
# You can also update all the plugins and themes
$ vendor/bin/wp plugin update --all
$ vendor/bin/wp theme update --all
```

If you get the following error:

```sh
Failed opening required 'google/appengine/api/urlfetch_service_pb.php'
```

You can set a `WP_CLI_PHP_ARGS` environment variable to add
`include_path` PHP configuration for wp-cli.

```sh
$ export WP_CLI_PHP_ARGS='-d include_path=vendor/google/appengine-php-sdk'
```

Then try the above commands again.

### Remove Plugins and Themes

First deactivate them in the production Dashboard, then remove them
completely locally. The next deployment will remove those files from
the production environment.

[sql-settings]: https://console.cloud.google.com/sql/instances
[mysql-client]: https://dev.mysql.com/doc/refman/5.7/en/mysql.html
[composer]: https://getcomposer.org/
[cloud-console]: https://console.cloud.google.com/
[cloud-storage-console]: https://console.cloud.google.com/storage
[cloud-sql-api-enable]: https://console.cloud.google.com/flows/enableapi?apiid=sqladmin
[app-engine-setting]: https://console.cloud.google.com/appengine/settings
[gcloud-sdk]: https://cloud.google.com/sdk/
[cloud-sql-proxy-download]: https://cloud.google.com/sql/docs/mysql/connect-external-app#install
[credentials-section]: https://console.cloud.google.com/apis/credentials/
[gcs-plugin]: https://wordpress.org/plugins/gcs/
