# A helper command for running WordPress on Google Cloud Platform

This is a small command line tool for downloading and configuring
WordPress for Google Cloud Platform. The script allows you to create a
working WordPress project for
[App Engine standard environment][appengine-standard] or
[App Engine flexible environment][appengine-flexible].

## Common Prerequisites

* Install [Composer][composer]
* Create a new Cloud Project on [Developers Console][dev-console]
* Enable Billing on that project
* Create App Engine default bucket at [App Engine Setting Page][app-engine-setting]
* Install [Google Cloud SDK][gcloud-sdk]

## Prerequisites for standard environment
* Install mysql

## Prerequisites for flexible environment
* [Enable Cloud SQL API][cloud-sql-api-enable] (For App Engine flexible environment)

## Project preparation

Configure Google Cloud SDK with your account and the Project.

```
$ gcloud auth login
...
...
$ gcloud config set project YOUR_PROJECT_ID
```

Then configure the App Engine default GCS bucket for later use. The
default App Engine bucket looks like
YOUR_PROJECT_ID.appspot.com. Change the default acl of that bucket as
follows:

```
$ gsutil defacl ch -u AllUsers:R gs://YOUR_PROJECT_ID.appspot.com
```

## Create and configure a Cloud SQL instance

If you will use App Engine flexible environment, create a Cloud SQL
2nd generation instance, and if you will use App Engine standard
environment, create a Cloud SQL 1st generation instance.

In this guide, we use `wp` for various resource names; the instance
name, the database name, and the user name.

### Create and configure a Cloud SQL 1st generation instance(for standard environment)

Go to the [SQL settings in the Cloud Console][sql-settings] and create
an instance `wp` and database named `wp`. Go to the Access Control ->
Users, then change the password for `root@localhost`. You will use
this password for accessing from App Engine application.

Also create the `wp` database in the local mysql server. The local
mysql instance is required to run `wp-cli` tool for
installing/upgrading plugins and themes.

### Create and configure a Cloud SQL 2nd generation instance(for flexible environment)

You can create a new Cloud SQL Second Generation instance with the
following command:

```
$ gcloud sql instances create wp \
  --activation-policy=ALWAYS \
    --tier=db-g1-small
```

Then change the root password for your instance:

```
$ gcloud sql instances set-root-password wp \
  --password YOUR_INSTANCE_ROOT_PASSWORD # Don't use this password!
```

To access this MySQL instance, we’ll use Cloud SQL Proxy. Please
download an appropriate binary from
[the download page][cloud-sql-proxy-download], make it executable.

If you haven’t created a service account for the project, please
create it on [the Credentials section][credentials-section] in the
Console (Choose a new service account). Download the JSON key file and
save it in a secure place.

Run the proxy by the following command:

```
$ cloud_sql_proxy \
  -dir /tmp/cloudsql \
    -instances=YOUR_PROJECT_ID:us-central1:wp=tcp:3306 \
      -credential_file=PATH_TO_YOUR_SERVICE_ACCOUNT_JSON
```

Now you can access to the Cloud SQL instance with the normal MySQL
client. Please create a new database and a user as follows:

```
$ mysql -h 127.0.0.1 -u root -p
mysql> create database wp;
mysql> create user 'wp'@'%' identified by 'PASSWORD'; // Don't use this password!
mysql> grant all on wp.* to 'wp'@'%';
mysql> exit
Bye
```

In the above example, I created a new database wp and a new user wp.

## How to use

First install the dependencies in this directory as follows:

```
$ composer install
```

If it complains about extensions, please install `phar` and `zip` PHP
extesions and retry.

Then run the helper command.

```
$ php wordpress-helper.php setup
```

The command asks you several questions, please answer them. Then
you'll have a new WordPress project. By default it will create
`my-wordpress-project` in the current directory.

## Run WordPress locally and create a new user (for standard environment)

If you chose the flexible environment, skip this step.

This step will create a basic database setup in your local mysql
server. This is required to use `wp-cli` tool.

CD into your WordPress project directory and run the following command
to run WordPress locally (be sure to keep the cloud SQL proxy
running):

```
$ cd my-wordpress-project
$ vendor/bin/wp(.bat) server --path=wordpress
```

Then access http://localhost:8080/. Follow the installation steps,
create the admin user and its password. Login to the Dashboard and
update if any of the plugins have update.

Now it’s ready for the first deployment.

## Deployment

You can deploy your WordPress project by the following command.

```
$ gcloud app deploy \
    --promote --stop-previous-version app.yaml cron.yaml
```

Then access your site, and continue the installation step. The URL is:
https://PROJECT_ID.appspot.com/

Go to the Dashboard, and in the Plugins page, activate the following
plugins:


- For standard environment
  - App Engine WordPress plugin (also set the e-mail address in its
    setting page)
  - Batcache Manager
- For flexible environment
  - Batcache Manager
  - GCS media plugin

After activating the plugins, try uploading a media and confirm the
image is uploaded to the GCS bucket.

## Check if the Batcache plugin is working

On the plugin page in the WordPress dashboard, you should see 2
drop-ins are activated; `advanced-cache.php` and `object-cache.php`.

To make sure it’s really working, you can open an incognito window and
visit the site because the cache plugin only serves from cache to
anonymous users. Then go to
[the memcache dashboard in the Cloud Console][memcache-dashboard] and
check the hit ratio and number of items in cache.

## Various workflows

### Install/Update plugins/themes

Because the wp-content directory on the server is read-only, you have
to do this locally. Run WordPress locally and update plugins/themes in
the local Dashboard, then deploy, then activate them in the production
Dashboard. You can also use the `wp-cli` utility as follows:

```
# To update all the plugins
$ vendor/bin/wp plugin update --all --path=wordpress
# To update all the themes
$ vendor/bin/wp theme update --all --path=wordpress
```

### Remove plugins/themes

First Deactivate them in the production Dashboard, then remove them
completely locally. The next deployment will remove those files from
the production environment.

### Update WordPress itself

Most of the case, just download the newest WordPress and overwrite the
existing wordpress directory. It is still possible that the existing
config files are not compatible with the newest WordPress, so please
update the config file manually in that case.

### Update the base image

We sometimes release the security update for
[the php-docker image][php-docker]. Then you’ll have to re-deploy your
WordPress instance to get the security update.

Enjoy your WordPress installation!

[appengine-standard]: https://cloud.google.com/appengine/docs/about-the-standard-environment
[appengine-flexible]: https://cloud.google.com/appengine/docs/flexible/
[sql-settings]: https://console.cloud.google.com/sql/instances
[memcache-dashboard]: https://console.cloud.google.com/appengine/memcache
[composer]: https://getcomposer.org/
[dev-console]: https://console.cloud.google.com/
[cloud-sql-api-enable]: https://console.cloud.google.com/flows/enableapi?apiid=sqladmin
[app-engine-setting]: https://console.cloud.google.com/appengine/settings
[gcloud-sdk]: https://cloud.google.com/sdk/
[cloud-sql-proxy-download]: https://cloud.google.com/sql/docs/external#appaccess
[credentials-section]: https://console.cloud.google.com/apis/credentials/
[php-docker]: https://github.com/googlecloudplatform/php-docker
