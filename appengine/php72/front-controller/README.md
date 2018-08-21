# Front Controllers on App Engine Standard for PHP 7.2

This app demonstrates how to implement a simple front controller for legacy
projects. The main code sample is in [`index.php`](index.php#L13). This is one
example of a front controller. See here for more examples:

 * [front controller implementation using the Slim Framework](../slim-framework/index.php#L26)
 * [front controller implementation for WordPress](../wordpress/files/gae-app.php#L3)
 * [front controller implementation using regular expressions](../grpc/index.php#L11)

## Setup

- Install the [Google Cloud SDK](https://developers.google.com/cloud/sdk/).

## Deploy

### Run Locally

You can run the sample locally using PHP's build-in web server:

```
# Run PHP's built-in web server
php -S localhost:8000
```

Browse to `localhost:8000` to see a list of examples to execute.

### Deploy with gcloud

Deploy the samples by doing the following:

```
gcloud config set project YOUR_PROJECT_ID
gcloud app deploy
gcloud app browse
```

The last command will open `https://{YOUR_PROJECT_ID}.appspot.com/`
in your browser. Browse to `/` to see a list of examples to execute.
