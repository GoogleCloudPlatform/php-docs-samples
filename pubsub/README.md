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

## Configuration

Run the following commands to create your pubsub subscription topic:

```sh
$ gcloud alpha pubsub topics create [your-topic-name]
1 topic(s) created successfully
 - projects/[your-project-name]/topics/[your-topic-name]
```

> We use **`php-pubsub-example`** as the topic name, but you can change this
> by setting the `PUBSUB_TOPIC` environment variable (see `app.yaml`)

Then you need to create your subscription to this topic by supplying
the endpoint to be notified when the topic is published to:

```
gcloud alpha pubsub subscriptions create [your-subscription-name] \
 --topic [your-topic-name] \
 --push-endpoint https://[your-project-name].appspot.com/receive_message \
 –-ack-deadline 30
```

## Deploy the application to App Engine

```
$ gcloud preview app deploy app.yaml --set-default --project YOUR_PROJECT_NAME
```

Then access the following URL:
  https://{YOUR_PROJECT_NAME}.appspot.com/

## Run the application locally

```
$ dev_appserver.py -A your-project-name .
```

## Contributing changes

* See [CONTRIBUTING.md](../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../LICENSE)


