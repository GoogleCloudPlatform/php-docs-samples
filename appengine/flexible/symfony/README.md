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
composer create-project symfony/framework-standard-edition:^3.0
```

# Integrate Stackdriver

Install some cloud libraries for Stackdriver integration

```sh
cd /path/to/symfony
composer require google/cloud-logging google/cloud-error-reporting
```

## Copy over App Engine files

For your app to deploy on App Engine Flexible, you will need to copy over some files in this
directory:

```sh
# clone this repo somewhere
git clone https://github.com/GoogleCloudPlatform/php-docs-samples /path/to/php-docs-samples

# create a directory for the event subscriber
mkdir -p /path/to/symfony/src/AppBundle/EventSubscriber

# copy the three files below to your Symfony project
cd /path/to/php-docs-samples/appengine/flexible/symfony/
cp app.yaml /path/to/symfony
cp app/config/config_prod.yml /path/to/symfony/app/config
cp src/AppBundle/EventSubscriber/ExceptionSubscriber.php \
    /path/to/symfony/src/AppBundle/EventSubscriber
```

The three files needed are as follows:

  1. [`app.yaml`](app.yaml) - The App Engine configuration for your project
  1. [`app/config/config_prod.yml`](app/config/config_prod.yml) - Symfony configurations for Stackdriver Logging
  1. [`src/AppBundle/EventSubscriber/ExceptionSubscriber.php`](src/AppBundle/EventSubscriber/ExceptionSubscriber.php) - Symfony configurations for Stackdriver Error Reporting

[1]: https://cloud.google.com/appengine/docs/flexible/
[2]: https://console.cloud.google.com
