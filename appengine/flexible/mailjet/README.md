# Mailjet PHP Sample Application for App Engine Flexible Environment.

## Description

This sample application demonstrates how to use [Mailjet with Google App Engine](https://cloud.google.com/appengine/docs/php/mail/).

## Setup

Before running this sample:

1. You will need a [Mailjet account](http://www.mailjet.com).
2. Update `MAILJET_APIKEY` and `MAILJET_SECRET` in `index.php` to match your
   Mailjet credentials.

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

## Run locally

you can run locally using PHP's built-in web server:

```sh
cd php-docs-samples/appengine/flexible/mailjet
php -S localhost:8080
```

Now you can view the app running at [http://localhost:8080](http://localhost:8080)
in your browser.
