# Google App Engine Taskqueue API

This sample application demonstrates how to use Google App Engine's
Taskqueue API.

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
gcloud app deploy app.yaml
gcloud app browse
```

The last command will open `https://{YOUR_PROJECT_ID}.appspot.com/`
in your browser.
