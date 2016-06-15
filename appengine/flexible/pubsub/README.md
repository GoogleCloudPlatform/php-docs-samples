# Google PubSub PHP Sample Application for App Engine Flexible Environment.

## Description

This sample demonstrates how to invoke PubSub from Google App Engine Flexible
Environment.

The sample code lives in [a parent pubsub directory](../../../pubsub).
Only two configuration files differ: `app.yaml` and `nginx-app.conf`.

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


## Configuration

- Edit `app.yaml`.  Replace `your-google-project-id` with your google project id.

- Copy `app.yaml` and `nginx-app.conf` into [../../../pubsub](../../../pubsub).  Ex:
```sh
~/gitrepos/php-docs-samples/appengine/flexible/pubsub$ cp -f app.yaml nginx-app.conf ../../../pubsub
~/gitrepos/php-docs-samples/appengine/flexible/pubsub$ cd ../../../pubsub/
~/gitrepos/php-docs-samples/pubsub$ 
```

## Deploy the application to App Engine

```
$ gcloud preview app deploy app.yaml --set-default --project YOUR_PROJECT_NAME
```

Then access the following URL:
  https://{YOUR_PROJECT_NAME}.appspot.com/

## Run the application locally

```
/usr/bin/php -S localhost:8910 -t web
```

## Contributing changes

* See [CONTRIBUTING.md](../../../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../../../LICENSE)


