# Google PubSub PHP Sample Application for App Engine Flexible Environment.

## Description

This sample demonstrates how to invoke PubSub from Google App Engine Flexible
Environment.

The sample code lives in [a parent pubsub directory](../../../pubsub/app).
Only two configuration files differ: `app.yaml` and `nginx-app.conf`.

## Register your application

## Configuration

- Edit `app.yaml`.  Replace `YOUR_PROJECT_ID` with your google project id.

- Copy `app.yaml` and `nginx-app.conf` into [../../../pubsub/app](../../../pubsub/app).  Ex:
```sh
~/gitrepos/php-docs-samples/appengine/flexible/pubsub$ cp -f app.yaml nginx-app.conf ../../../pubsub/app
~/gitrepos/php-docs-samples/appengine/flexible/pubsub$ cd ../../../pubsub/app
~/gitrepos/php-docs-samples/pubsub$
```

- Follow the [Prerequisite Instructions](../../../pubsub/app/README.md#prerequisites)

## Deploy the application to App Engine

```
$ gcloud app deploy --set-default --project YOUR_PROJECT_ID
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


