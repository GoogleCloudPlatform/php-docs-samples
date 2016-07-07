# Google PubSub PHP Sample Application

## Description

Note: The push endpoints don't work with the App Engine's local
devserver. The push notifications will go to an HTTP URL on the App
Engine server even when you run this sample locally. So we recommend
you deploy and run the app on App Engine.
TODO(tmatsuo): Better implementation for devserver.

## Register your application

- Go to
  [Google Developers Console](https://console.developers.google.com/project)
  and create a new project. This will automatically enable an App
  Engine application with the same ID as the project.

- Enable the "Google Cloud Pub/Sub" API under "APIs & auth > APIs."
- Enable the "Google Cloud Datastore" API under "APIs & auth > APIs."
- For local development also follow the instructions below.
  - Go to "Credentials" and create a new Service Account.
  - Select "Generate new JSON key", then download a new JSON file.
  - Set the following environment variable:
    - `GOOGLE_APPLICATION_CREDENTIALS`: the file path to the downloaded JSON file.

## Prerequisites

- Install [`composer`](https://getcomposer.org)
- Install the App Engine Python SDK.
  We recommend you install
  [Cloud SDK](https://developers.google.com/cloud/sdk/) rather than
  just installing App Engine SDK.

- Install Google API client library for PHP into 'lib' directory by running:

```
$ composer install
```

## Deploy the application to App Engine

```
$ gcloud app deploy app.yaml --set-default --project YOUR_PROJECT_NAME
```

Then access the following URL:
  https://{YOUR_PROJECT_NAME}.appspot.com/

## Deploy the application to App Engine Flexible Environment.

See the instructions [here](../appengine/flexible/pubsub/README.md).

## Run the application locally

```
$ dev_appserver.py -A your-project-name .
```

## Contributing changes

* See [CONTRIBUTING.md](../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../LICENSE)


