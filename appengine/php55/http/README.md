# HTTP Requests & Google App Engine

This sample application demonstrates how to make [HTTP Requests with Google App Engine](https://cloud.google.com/appengine/docs/php/outbound-requests).

## Setup

- Install [`composer`](https://getcomposer.org)
- Install dependencies by running:

```sh
composer install
```

- Install the [Google Cloud SDK](https://developers.google.com/cloud/sdk/).

## Deploy to App Engine

**Run Locally**

Run the sample with [`dev_appserver.py`](https://cloud.google.com/appengine/docs/php/tools/using-local-server):

```
cd /path/to/php-docs-samples/appengine/standard/http
dev_appserver.py .
```

Now browse to `http://localhost:8080` to view the sample.

**Deploy with gcloud**

```
gcloud config set project YOUR_PROJECT_ID
gcloud app deploy
gcloud app browse
```

The last command will open `https://{YOUR_PROJECT_ID}.appspot.com/`
in your browser.

## Using the App

This app shows you how to make http requests in Google App Engine. To use cURL,
modify the `php.ini` file in the root of this project and uncomment one of the
valid cURL extensions. [Read our documentation] to understand the difference
between using cURL and cURLite.
