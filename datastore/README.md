# Google Datastore PHP Sample Application

A simple app to make calls to the Datastore API

## Register your application

- Go to
  [Google Developers Console](https://console.developers.google.com/project)
  and create a new project. This will automatically enable an App
  Engine application with the same ID as the project.

- Enable the "Google Cloud Datastore API" under "APIs & auth > APIs."

- edit `app.yaml` and change `YOUR_GCP_PROJECT_ID` to your App Engine project ID

- For local development also follow the instructions below.

  - Go to "Credentials" and create a new Service Account.

  - Select "Generate new JSON key", then download a new JSON file.

  - Set the following environment variables:

    - `GOOGLE_APPLICATION_CREDENTIALS`: the file path to the downloaded JSON file.
    - `GCP_PROJECT_ID`: Your app engine project ID

## Prerequisites

- Install [`composer`](https://getcomposer.org)
- Install Google API client library for PHP by running:

```sh
composer install
```

## Run locally

you can run locally using PHP's built-in web server:

```sh
cd php-docs-samples/datastore
export GOOGLE_APPLICATION_CREDENTIALS=/path/to/credentials.json
export GCP_PROJECT_ID=my-project-id
php -S localhost:8080 -t web/
```

Now you can view the app running at [http://localhost:8080](http://localhost:8080)
in your browser.

## Deploy to App Engine

**Prerequisites**

- Install the App Engine PHP SDK.
  We recommend you install
  [Cloud SDK](https://developers.google.com/cloud/sdk/) rather than
  just installing App Engine SDK.

**Deploy with gcloud**

```
$ gcloud app deploy app.yaml --set-default --project YOUR_GCP_PROJECT_ID
```

Then access the following URL:
  https://{YOUR_GCP_PROJECT_ID}.appspot.com/

### Run for App Engine locally

```
$ dev_appserver.py -A your-project-id .
```

## Contributing changes

* See [CONTRIBUTING.md](../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../LICENSE)


