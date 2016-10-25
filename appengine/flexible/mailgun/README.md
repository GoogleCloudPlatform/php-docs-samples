# Mailgun PHP Sample Application for App Engine Flexible Environment.

## Description

This sample application demonstrates how to use [Mailgun with Google App Engine](https://cloud.google.com/appengine/docs/php/mail/).

## Setup

Before running this sample:

1. You will need a [Mailgun account](http://www.mailgun.com/google).
2. Update `MAILGUN_DOMAIN` and `MAILGUN_APIKEY` in `index.php` to match your
   Mailgun credentials. You can use your account's sandbox domain.

## Prerequisites

- Install [`composer`](https://getcomposer.org)
- Install dependencies by running:

```sh
composer install
```

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

## Run Locally

```
cd /path/to/php-docs-samples/appengine/flexible/mailgun
php -S localhost:8080
```

Now browse to `http://localhost:8080` to view the sample.
