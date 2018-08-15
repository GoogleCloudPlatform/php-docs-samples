# Deploy WordPress to App Engine for PHP 7.2

This is a small command line tool for downloading and configuring
WordPress on App Engine for PHP 7.2. The script allows you to create a
working WordPress project for the
[App Engine standard environment][appengine-standard]. For deploying
WordPress to the [App Engine flexible environment][appengine-flexible],
refer to the example at [appengine/standard/wordpress][../../flexible/wordpress]

## Common Prerequisites

* Install [Composer][composer]
* Create a new Cloud Project using the [Cloud Console][cloud-console]
* Enable Billing on that project
* [Enable Cloud SQL API][cloud-sql-api-enable]
* Install [Google Cloud SDK][gcloud-sdk]
* Install the [mysql-client][mysql-client] command line tool

## Project preparation

Configure Google Cloud SDK with your account and the appropriate project ID:

```
$ gcloud init
```

Create an App Engine application within your new project:

```
$ gcloud app create
```

Then configure the App Engine default GCS bucket for later use. The default App
Engine bucket is named YOUR_PROJECT_ID.appspot.com. Change the default Access
Control List (ACL) of that bucket as follows:

```
$ gsutil defacl ch -u AllUsers:R gs://YOUR_PROJECT_ID.appspot.com
```

### Create and configure a Cloud SQL for MySQL 2nd generation instance

Note: In this guide, we use `wordpress` for the instance name and the database
name. We use `root` for the database user name.

Create a new Cloud SQL for MySQL Second Generation instance with the following
command:

```sh
$ gcloud sql instances create wordpress \
    --activation-policy=ALWAYS \
    --tier=db-n1-standard-1
```

Note: you can choose `db-f1-micro` or `db-g1-small` instead of
`db-n1-standard-1` for the Cloud SQL machine type, especially for the
development or testing purpose. However, those machine types are not
recommended for production use and are not eligible for Cloud SQL SLA
coverage. See our [Cloud SQL SLA](https://cloud.google.com/sql/sla)
for more details.

Then change the root password for your instance:

```sh
$ gcloud sql users set-password root \
    --host=% \
    --instance wordpress \
    --password=YOUR_INSTANCE_ROOT_PASSWORD # Don't use this password!
```

You will also need to create the database you want your WordPress site to use:

```sh
$ gcloud sql databases create wordpress --instance wordpress
```

## SetUp

First install the dependencies in this directory as follows:

```sh
$ composer install
```

If it complains about extensions, please install `phar` and `zip` PHP
extensions and retry.

### Create a new WordPress Project

To download WordPress and set it up for Google Cloud, run the `create` command:

```sh
$ php wordpress.php create
```

The command asks you several questions, please answer them. Then you'll have a
new WordPress project. By default it will create `my-wordpress-project` in the
current directory.

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

CD into your WordPress project directory and run the following command to
deploy:

```sh
$ gcloud app deploy \
    --promote --stop-previous-version app.yaml cron.yaml
```

Then access your site, and continue the installation step. The URL is:
https://PROJECT_ID.appspot.com/

Go to the Dashboard at https://PROJECT_ID.appspot.com/wp-admin. On the Plugins page, activate the following plugins:

  - Google App Engine for WordPress (also set the e-mail address in its
    settings page)

After activating the plugins, try uploading a media object in a new post
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

## Various workflows

### Install/Update Wordpress, plugins, and themes

Because the wp-content directory on the server is read-only, you have
to do this locally. Run WordPress locally and update plugins/themes in
the local Dashboard, then deploy, then activate them in the production
Dashboard. You can also use the `wp-cli` utility as follows (be sure to keep
the cloud SQL proxy running):

```
# Install the wp-cli utility
$ composer require wp-cli/wp-cli
# Now you can run the "wp" command to update Wordpress itself
$ vendor/bin/wp core update --path=wordpress
# You can also update all the plugins and themes
$ vendor/bin/wp plugin update --all
$ vendor/bin/wp theme update --all
```

If you get the following error:

```
Failed opening required 'google/appengine/api/urlfetch_service_pb.php'
```

You can set a `WP_CLI_PHP_ARGS` environment variable to add
`include_path` PHP configuration for wp-cli.

```
$ export WP_CLI_PHP_ARGS='-d include_path=vendor/google/appengine-php-sdk'
```

Then try the above commands again.

### Remove plugins/themes

First Deactivate them in the production Dashboard, then remove them
completely locally. The next deployment will remove those files from
the production environment.

### Update the base image

We sometimes release a security update for
[the php-docker image][php-docker]. You have to re-deploy your
WordPress instance to get the security update.

Enjoy your WordPress installation!

[appengine-standard]: https://cloud.google.com/appengine/docs/about-the-standard-environment
[appengine-flexible]: https://cloud.google.com/appengine/docs/flexible/
[sql-settings]: https://console.cloud.google.com/sql/instances
[mysql-client]: https://dev.mysql.com/doc/refman/5.7/en/mysql.html
[composer]: https://getcomposer.org/
[cloud-console]: https://console.cloud.google.com/
[cloud-storage-console]: https://www.console.cloud.google.com/storage
[cloud-sql-api-enable]: https://console.cloud.google.com/flows/enableapi?apiid=sqladmin
[app-engine-setting]: https://console.cloud.google.com/appengine/settings
[gcloud-sdk]: https://cloud.google.com/sdk/
[cloud-sql-proxy-download]: https://cloud.google.com/sql/docs/mysql/connect-external-app#install
[credentials-section]: https://console.cloud.google.com/apis/credentials/
[php-docker]: https://github.com/googlecloudplatform/php-docker
