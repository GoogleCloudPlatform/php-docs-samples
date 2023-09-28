# Google PubSub PHP Sample Application

## Description

Note: The push endpoints don't work with the App Engine's local
devserver. The push notifications will go to an HTTP URL on the App
Engine server even when you run this sample locally. So we recommend
you deploy and run the app on App Engine.

## Register your application

- Go to
  [Google Developers Console](https://console.developers.google.com/project)
  and create a new project. This will automatically enable an App
  Engine application with the same ID as the project.

- Enable the "Google Cloud Pub/Sub" API under "APIs & auth > APIs."

## Prerequisites

- Install [`composer`](https://getcomposer.org)
- Install the App Engine Python SDK.
  We recommend you install
  [Cloud SDK](https://developers.google.com/cloud/sdk/) rather than
  just installing App Engine SDK.
- Create the topic "php-example-topic" and create a subscription to that topic
  with the name "php-example-subscription".
  - Use the [pubsub CLI](../cli) or the
    [Developer Console](https://console.developer.google.com)
  - To use Push Subscriptions, register your subscription with the
    endpoint `https://{YOUR_PROJECT_NAME}.appspot.com/receive_message`
- Install dependencies by running:

```
$ composer install
```

## Local Development

- Go to "Credentials" and create a new Service Account.
- Select "Generate new JSON key", then download a new JSON file.
- Set the following environment variable:
  - `GOOGLE_APPLICATION_CREDENTIALS`: the file path to the downloaded JSON file.
  - `GCLOUD_PROJECT`: your project ID.

Run the PHP build-in web server with the following command:

```
$ php -S localhost:8080
```

Now browse to [localhost:8080](http://localhost:8080) in your browser.

## Deploy to App Engine Standard

- Change `YOUR_PROJECT_ID` in `app.yaml` to your project ID.

Run the following gcloud command to deploy your app:

```
$ gcloud app deploy
```

Then access the following URL:
  https://{YOUR_PROJECT_NAME}.appspot.com/

## Deploy to App Engine Flexible

- Change `YOUR_PROJECT_ID` in `app.yaml.flexible` to your project ID.

Run the following gcloud command to deploy your app:

```
$ gcloud app deploy app.yaml.flexible
```

Then access the following URL:
  https://{YOUR_PROJECT_NAME}.appspot.com/

## Run using Dev Appserver

```
$ dev_appserver.py -A your-project-name .
```

## Contributing changes

* See [CONTRIBUTING.md](../../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../../LICENSE)


