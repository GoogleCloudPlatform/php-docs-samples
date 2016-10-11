Symfony on App Engine Flexible Environment
==========================================

## Overview

This guide will help you deploy Symfony on [App Engine Flexible Environment][1]

## Prerequisites

Before setting up Symfony on App Engine, you will need to complete the following:

  1. Create a [Google Cloud Platform project][2]. Note your **Project ID**, as you will need it
     later.

## Install Symfony

Use composer to download Symfony Standard and its dependencies

```sh
composer create-project symfony/symfony:^3.0
```

## Copy over App Engine files

For your app to deploy on App Engine Flexible, you will need to copy over some files in this
directory:

```sh
# clone this repo somewhere
git clone https://github.com/GoogleCloudPlatform/php-docs-samples /path/to/php-docs-samples

# copy the two files below to the root directory of your Symfony project
cd /path/to/php-docs-samples/appengine/flexible/symfony/
cp ./{app.yaml,nginx-app.conf} /path/to/symfony
```

The two files needed are as follows:

  1. [`app.yaml`](app.yaml) - The App Engine configuration for your project
  1. [`nginx-app.conf`](nginx-app.conf) - Nginx web server configuration needed for `Symfony`

Finally, you need to have a few scripts run after your application deploys.
You can do this by providing the path to your `composer.json` to the `add_composer_scripts.php`
script:

```sh
php ./add_composer_scripts.php /path/to/symfony/composer.json
```
This will add the following scripts to your project's `composer.json`:

```json
{
    "scripts": {
        "post-deploy-cmd": [
            "chmod -R ug+w /app/var"
        ]
    }
}
```

[1]: https://cloud.google.com/appengine/docs/flexible/
[2]: https://console.cloud.google.com
