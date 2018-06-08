# Cloud SQL & Google App Engine

This sample application demonstrates how to use [Cloud SQL with Google App Engine](https://cloud.google.com/appengine/docs/php/cloud-sql/).

## Setup

Before you can run or deploy the sample, you will need to do the following:

1. Create a [Second Generation Cloud SQL](https://cloud.google.com/sql/docs/create-instance) instance. You can do this from the [Cloud Console](https://console.developers.google.com) or via the [Cloud SDK](https://cloud.google.com/sdk). To create it via the SDK use the following command:

        $ gcloud sql instances create YOUR_INSTANCE_NAME

1. Set the root password on your Cloud SQL instance:

        $ gcloud sql instances set-root-password YOUR_INSTANCE_NAME --password YOUR_INSTANCE_ROOT_PASSWORD

1. Update the connection string in `app.yaml` with your configuration values. These values are used when the application is deployed.

## Run locally

You can connect to a local database instance by setting the `CLOUDSQL_` environment variables
to your local instance. Alternatively, you can set them to your Cloud instances, but you will need
to create a firewall rule for this, which may be a safety concern.

```sh
cd php-docs-samples/appengine/php72/cloudsql

# set local connection parameters
export CLOUDSQL_USERNAME=root
export CLOUDSQL_PASSWORD=
export CLOUDSQL_DSN="mysql:host=localhost;dbname=guestbook"

php -S localhost:8080
```

> be sure the `CLOUDSQL_` environment variables are appropriate for your MySQL or PostgreSQL instance.

Now you can view the app running at [http://localhost:8080](http://localhost:8080)
in your browser.

## Deploy to App Engine

**Prerequisites**

- Install the [Google Cloud SDK](https://developers.google.com/cloud/sdk/).

**Deploy with gcloud**

```
gcloud config set project YOUR_PROJECT_ID
gcloud app deploy
gcloud app browse
```

The last command will open `https://{YOUR_PROJECT_ID}.appspot.com/`
in your browser.
