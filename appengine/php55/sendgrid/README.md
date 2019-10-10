# Sendgrid and Google App Engine Standard (PHP 5.5)

This sample application demonstrates how to use
[Sendgrid with Google App Engine Standard Environment](https://cloud.google.com/appengine/docs/standard/php/mail/sendgrid).

## Setup

Before running this sample:

1. You will need a [SendGrid account](http://sendgrid.com/partner/google).
2. Update `SENDGRID_SENDER` and `SENDGRID_API_KEY` in `app.yaml` to match your
   SendGrid credentials. You can use your account's sandbox domain.

## Prerequisites

- Install [`composer`](https://getcomposer.org)
- Install dependencies by running:

```sh
composer install
```

## Deploy to App Engine

**Prerequisites**

- Install the [Google Cloud SDK](https://developers.google.com/cloud/sdk/).

**Run Locally**
```sh
export SENDGRID_APIKEY=your-sendgrid-api-key
export SENDGRID_SENDER=somebody@yourdomain.com
php -S localhost:8000 -t .
```

**Deploy with gcloud**
```
gcloud config set project YOUR_PROJECT_ID
gcloud app deploy
gcloud app browse
```

The last command will open `https://{YOUR_PROJECT_ID}.appspot.com/`
in your browser.
