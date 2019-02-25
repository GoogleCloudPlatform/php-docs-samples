# Run WordPress on App Engine Flexible

This is a small command line tool for downloading and configuring
WordPress for Google Cloud Platform. The script allows you to create a
working WordPress project for the
[App Engine flexible environment][appengine-flexible]. For deploying
WordPress to the [App Engine standard environment][appengine-standard],
refer to the example at [appengine/standard/wordpress](../../standard/wordpress)

## Common Prerequisites

* Install [Composer][composer]
* Create a new Cloud Project using the [Cloud Console][cloud-console]
* Enable Billing on that project
* [Enable Cloud SQL API][cloud-sql-api-enable]
* Install [Google Cloud SDK][gsubl ..cloud-sdk]
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

Note: In this guide, we use `wp` for various resource names; the instance
name, the database name, and the user name.

Create a new Cloud SQL for MySQL Second Generation instance with the following
command:

```
$ gcloud sql instances create wp \
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

```
$ gcloud sql users set-password root --host=% \
  --instance wp --password=YOUR_INSTANCE_ROOT_PASSWORD # Don't use this password!
```

To access this MySQL instance, use Cloud SQL Proxy. [Download][cloud-sql-proxy-download]
it to your local computer and make it executable.

Go to the [the Credentials section][credentials-section] of your project in the
Console. Click 'Create credentials' and then click 'Service account key.' For
the Service account, select 'App Engine app default service account.' Then
click 'Create' to create and download the JSON service account key to your
local machine. Save it to a safe place.

Run the proxy by the following command:

```
$ cloud_sql_proxy \
  -dir /tmp/cloudsql \
    -instances=YOUR_PROJECT_ID:us-central1:wp=tcp:3306 \
      -credential_file=PATH_TO_YOUR_SERVICE_ACCOUNT_JSON_FILE
```

Now you can access the Cloud SQL instance with the MySQL client in a separate
command line tab. Create a new database and a user as follows:

```
$ mysql -h 127.0.0.1 -u root -p
mysql> create database wp;
mysql> create user 'wp'@'%' identified by 'PASSWORD'; // Don't use this password!
mysql> grant all on wp.* to 'wp'@'%';
mysql> exit
```

## How to use

First install the dependencies in this directory as follows:

```
$ composer install
```

If it complains about extensions, please install `phar` and `zip` PHP
extensions and retry.

Then run the helper command.

```
$ php wordpress.php setup
```

The command asks you several questions, please answer them. Then you'll have a
new WordPress project. By default it will create `my-wordpress-project` in the
current directory.

## Deployment

CD into your WordPress project directory and run the following command to
deploy:

```
$ cd my-wordpress-project
$ gcloud app deploy \
    --promote --stop-previous-version app.yaml cron.yaml
```

Then access your site, and continue the installation step. The URL is:
https://PROJECT_ID.appspot.com/

Go to the Dashboard at https://PROJECT_ID.appspot.com/wp-admin. On the Plugins page, activate the following
plugins:

  - GCS media plugin

After activating the plugins, try uploading a media object in a new post
and confirm the image is uploaded to the GCS bucket by visiting the
[Google Cloud console's Storage page][cloud-storage-console].

## Various workflows

### Install/Update Wordpress, plugins, and themes

Because the wp-content directory on the server is read-only, you have
to do this locally. Run WordPress locally and update plugins/themes in
the local Dashboard, then deploy, then activate them in the production
Dashboard. You can also use the `wp-cli` utility as follows (be sure to keep
the cloud SQL proxy running):

```
# To update Wordpress itself
$ vendor/bin/wp core update --path=wordpress
# To update all the plugins
$ vendor/bin/wp plugin update --all --path=wordpress
# To update all the themes
$ vendor/bin/wp theme update --all --path=wordpress
```

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
