# Mailgun & Google App Engine

This sample application demonstrates how to use [Mailgun with Google App Engine](https://cloud.google.com/appengine/docs/php/mail/).

## Setup

Before running this sample:

1. You will need a [Mailgun account](http://www.mailgun.com/google).
2. Update the `MAILGUN_DOMAIN_NAME` and `MAILGUN_API_KEY` constants in `index.php`.
   You can use your account's sandbox domain.

## Prerequisites

- Install [`composer`](https://getcomposer.org)
- Install dependencies by running:

```sh
composer install
```

## Run locally

you can run locally using PHP's built-in web server:

```sh
cd php-docs-samples/appengine/standard/mailgun
php -S localhost:8080
```

Now you can view the app running at [http://localhost:8080](http://localhost:8080)
in your browser.

## Deploy to App Engine

**Prerequisites**

- Install the [Google Cloud SDK](https://developers.google.com/cloud/sdk/).

**Deploy with gcloud**

```
gcloud config set project YOUR_PROJECT_ID
gcloud preview app deploy
gcloud preview app browse
```

The last command will open `https://{YOUR_PROJECT_ID}.appspot.com/`
in your browser.
