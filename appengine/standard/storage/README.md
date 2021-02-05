# Cloud Storage on App Engine Standard for PHP 7.2

This sample application demonstrates how to use [Cloud Storage on App Engine for PHP 7.2](https://cloud.google.com/appengine/docs/standard/php7/using-cloud-storage).

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

1. Set `<your-bucket-name>` in `app.yaml` to the name of your Cloud Storage Bucket.

## Run Locally

First, set the `GOOGLE_APPLICATION_CREDENTIALS` environment variable to the
path to a set of downloaded
[service account credentials](https://cloud.google.com/docs/authentication/production#obtaining_and_providing_service_account_credentials_manually).

Next, set the `GOOGLE_STORAGE_BUCKET`environment variable to the name of a
Cloud Storage bucket in the same project as the credentials you downloaded. 
Make sure the service account you created has access.

Finally, run the PHP built-in web server to serve the demo app:

```
php -S localhost:8080
```

Now browse to `http://localhost:8080` to view the sample.

## Deploy to App Engine

**Prerequisites**

- Install the [Google Cloud SDK](https://developers.google.com/cloud/sdk/).

**Deploy with gcloud**

```
gcloud config set project YOUR_PROJECT_ID
gcloud app deploy
gcloud app browse
```

The last command will open `https://{YOUR_PROJECT_ID}.appspot.com/`
in your browser.
