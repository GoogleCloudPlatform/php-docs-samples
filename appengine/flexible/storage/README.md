# Cloud Storage & Google App Engine

This sample application demonstrates how to use [Cloud Storage with Google App Engine](https://cloud.google.com/appengine/docs/php/googlestorage/).

## Setup

Before running this sample:

## Prerequisites

- Install [`composer`](https://getcomposer.org)
- Install dependencies by running:

```sh
composer install
```

## Setup

Before you can run or deploy the sample, you will need to do the following:

1. Set `<your-bucket-name>` in `index.php` to the name of your Cloud Storage Bucket.

## Deploy to App Engine

**Prerequisites**

- Install the [Google Cloud SDK](https://developers.google.com/cloud/sdk/).

**Run Locally**

Create a local directory for the Dev AppServer to use for Cloud Storage:

```
mkdir /tmp/gs
```

> Note: This directory can be wherever you like, as long as it's consistent with
  the `--storage_path` option below.

Run the sample with `dev_appserver.py`:

```
cd /path/to/php-docs-samples/appengine/standard/storage
dev_appserver.py --php_executable=/usr/local/bin/php-cgi --storage_path=/tmp/gs .
```

> Note: Your PHP executable path may be different than the one above.

Now browse to `http://localhost:8080` to view the sample.

**Deploy with gcloud**

```
gcloud config set project YOUR_PROJECT_ID
gcloud app deploy
gcloud app browse
```

The last command will open `https://{YOUR_PROJECT_ID}.appspot.com/`
in your browser.
