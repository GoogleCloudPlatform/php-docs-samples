Symfony on Managed VMs
======================

## Overview

This guide will help you deploy Symfony on [App Engine Managed VMs][1]

## Prerequisites

Before setting up Symfony on Managed VMs, you will need to complete the following:

  1. Create a [Google Cloud Platform project][2]. Note your **Project ID**, as you will need it
     later.

## Install Symfony

Use composer to download Symfony Standard and its dependencies

```sh
composer create-project symfony/symfony:^3.0
```

## Copy over App Engine files

For your app to deploy on App Engine Managed VMs, you will need to copy over the files in this
directory:

```sh
# clone this repo somewhere
git clone https://github.com/GoogleCloudPlatform/php-docs-samples /path/to/php-docs-samples

# copy the four files below to the root directory of your Symfony project
cd /path/to/php-docs-samples/managed_vms/symfony/
cp ./{app.yaml,php.ini,Dockerfile,nginx-app.conf} /path/to/symfony
```

The four files needed are as follows:

  1. [`app.yaml`](app.yaml) - The App Engine configuration for your project
  1. [`Dockerfile`](Dockerfile) - Container configuration for the PHP runtime
  1. [`php.ini`](php.ini) - Optional ini used to extend the runtime configuration.
  1. [`nginx-app.conf`](nginx-app.conf) - Nginx web server configuration needed for `Symfony`

[1]: https://cloud.google.com/appengine/docs/managed-vms/
[2]: https://console.cloud.google.com
