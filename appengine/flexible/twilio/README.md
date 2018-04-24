# Twilio and Google App Engine Flexible Environment

This sample application demonstrates how to use [Twilio with Google App Engine](https://cloud.google.com/appengine/docs/flexible/php/using-sms-and-voice-services-via-twilio).

## Setup

Before running this sample:

1. You will need a [Twilio account](https://www.twilio.com/user/account).
1. Update `TWILIO_ACCOUNT_SID` and `TWILIO_AUTH_TOKEN` in `app.yaml` to match your
   Twilio credentials. These can be found in your [account settings]
   (https://www.twilio.com/user/account/settings)
1. Update `TWILIO_FROM_NUMBER` in `app.yaml` with a number you have authorized
   for sending messages. Follow [Twilio's documentation]
   (https://www.twilio.com/user/account/phone-numbers/getting-started) to set
   this up.

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
export TWILIO_ACCOUNT_SID=your-account-sid
export TWILIO_AUTH_TOKEN=your-auth-token
export TWILIO_FROM_NUMBER=your-twilio-number
cd php-docs-samples/appengine/flexible/twilio
php -S localhost:8080
```

Now you can view the app running at [http://localhost:8080](http://localhost:8080)
in your browser.
