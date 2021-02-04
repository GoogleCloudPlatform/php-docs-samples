# Stackdriver Trace on App Engine Standard for PHP 7.2

This app demonstrates how to set up Stackdriver Trace on App Engine Standard
for PHP 7.2.

## Setup

- Install [`composer`](https://getcomposer.org)
- Install dependencies by running:

    ```sh
    composer install
    ```

- Install the [Google Cloud SDK](https://developers.google.com/cloud/sdk/).

## Deploy

### Run Locally

You can run these samples locally using PHP's build-in web server:

```
# export environemnt variables locally which are set by app engine when deployed
export GOOGLE_CLOUD_PROJECT=YOUR_PROJECT_ID

# Run PHP's built-in web server
php -S localhost:8000
```

You will then be able to see your application traces in the
[Trace UI](https://console.cloud.google.com/traces/overview).

### Deploy with gcloud

Deploy the samples by doing the following:

```
gcloud config set project YOUR_PROJECT_ID
gcloud app deploy
gcloud app browse
```

The last command will open `https://{YOUR_PROJECT_ID}.appspot.com/`
in your browser. Browse to `/` to execute a trace. You will then be able to see
your traces in the [Trace UI](https://console.cloud.google.com/traces/overview).
