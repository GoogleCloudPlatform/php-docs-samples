Drupal 8 on Managed VMs
=======================

## Overview

This guide will help you deploy Drupal 8 on [App Engine Managed VMs][1]

## Prerequisites

Before setting up Drupal 8 on Managed VMs, you will need to complete the following:

  1. Create a [Google Cloud Platform project][2]. Note your **Project ID**, as you will need it
     later.
  1. Create a [Google Cloud SQL instance][3]. You will use this as your Drupal MySQL backend.

## Install Drupal 8

### Download

Use the [Drupal 8 Drush CLI][4] to install a drupal project. This can be installed locally
by running `composer install` in this directory:

```sh
composer install
./vendor/bin/drush
```

Now you can run the command to download drupal:

```sh
cd /path/to/drupal
/path/to/drush dl drupal
```

Alternatively, you can download a compressed file of Druapl 8 from the [Drupal Website][5].

### Installation

  1. Set up your Drupal 8 instance using the web interface
  ```sh
  cd /path/to/drupal
  php -S localhost:8080
  ```
  Open [http://localhost:8080](http://localhost:8080) in your browser after running these steps

  1. You can also try setting up your Drupal 8 instance using [Drush][4]
  ```sh
  cd /path/to/drupal
  /path/to/drush site-install \
    --locale=en \
    --db-path=mysql://user@pass:host/db_name \
    --site-name='My Drupal Site On Google' \
    --site-mail=you@example.com \
    --account-name admin \
    --account-mail you@example.com \
    --account-pass admin
  ```

You will want to use the Cloud SQL credentials you created in the **Prerequisites** section as your
Drupal backend.

## Copy over App Engine files

For your app to deploy on App Engine Managed VMs, you will need to copy over the files in this
directory:

```sh
# clone this repo somewhere
git clone https://github.com/GoogleCloudPlatform/php-docs-samples /path/to/php-docs-samples
cd /path/to/php-docs-samples/

# copy the four files below to the root directory of your Drupal project
cp managed_vms/drupal8/{app.yaml,php.ini,nginx-app.conf} /path/to/drupal
```

The four files needed are as follows:

  1. [`app.yaml`](app.yaml) - The App Engine configuration for your project
  1. [`php.ini`](php.ini) - Optional ini used to extend the runtime configuration.
  1. [`nginx-app.conf`](nginx-app.conf) - Nginx web server configuration needed for `Drupal 8`

## Disable CSS and JS Cache

For now, you need to disable the CSS and JS preprocessed caching that Drupal 8 enables by default.
To do this, go to `/admin/config/development/performance` and deselect the two
chechboxes (`Aggregate CSS files` and `Aggregate JS files`) under **Bandwidth Optimizations**.

Alternatively, you can use [Drush][4] to change this config setting:

```sh
# this command must be run inside the root directory of a drupal project
$ cd /path/to/drupal
$ /path/to/drush pm-enable config -y
$ /path/to/drush config-set system.performance css.preprocess 0
$ /path/to/drush config-set system.performance js.preprocess 0
```

This will change the values `preprocess` under `css` and `js` to `false`.

[1]: https://cloud.google.com/appengine/docs/managed-vms/
[2]: https://console.cloud.google.com
[3]: https://cloud.google.com/sql/docs/getting-started
[4]: http://docs.drush.org/en/master/install/
[5]: https://www.drupal.org/8/download