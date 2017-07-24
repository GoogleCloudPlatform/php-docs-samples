Drupal 8 on App Engine Flexible Environment
===========================================

## Overview

This guide will help you deploy Drupal 8 on [App Engine Flexible][1]

## Prerequisites

Before setting up Drupal 8 on App Engine Flexible, you will need to complete the following:

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

Alternatively, you can download a compressed file of Drupal 8 from the [Drupal Website][5].

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

## Add app.yaml

Add a file `app.yaml` with the following contents to the root of your Drupal project:

```yaml
runtime: php
env: flex
```

`app.yaml` is the App Engine configuration for your project.

## Disable CSS and JS Cache

For now, you need to disable the CSS and JS preprocessed caching that Drupal 8 enables by default.
To do this, go to `/admin/config/development/performance` and deselect the two
chechboxes (`Aggregate CSS files` and `Aggregate JS files`) under **Bandwidth Optimizations**.

Alternatively, you can use [Drush][4] to change this config setting:

```sh
# this command must be run inside the root directory of a drupal project
cd /path/to/drupal
/path/to/drush pm-enable config -y
/path/to/drush config-set system.performance css.preprocess 0
/path/to/drush config-set system.performance js.preprocess 0
```

This will change the values `preprocess` under `css` and `js` to `false`.

[1]: https://cloud.google.com/appengine/docs/flexible/
[2]: https://console.cloud.google.com
[3]: https://cloud.google.com/sql/docs/getting-started
[4]: http://docs.drush.org/en/master/install/
[5]: https://www.drupal.org/8/download
