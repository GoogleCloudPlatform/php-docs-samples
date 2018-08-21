# Cloud SQL on App Engine Standard for PHP 7.2

This sample application demonstrates how to use [Cloud SQL on App Engine for PHP 7.2](https://cloud.google.com/appengine/docs/standard/php7/using-cloud-sql).

## Setup

Before you can run or deploy the sample, you will need to do the following:

1. Create a [Second Generation Cloud SQL](https://cloud.google.com/sql/docs/create-instance)
   instance. You can do this from the [Cloud Console](https://console.developers.google.com)
   or via the [Cloud SDK](https://cloud.google.com/sdk). To create it via the
   SDK use the following command:

        $ gcloud sql instances create YOUR_INSTANCE_NAME

1. Create a database for the sample, for instance `cloudsql_sample`:

        $ gcloud sql databases create cloudsql_sample --instance=YOUR_INSTANCE_NAME

1. Set the root password on your Cloud SQL instance:

        $ gcloud sql users set-password root --host % --instance YOUR_INSTANCE_NAME --password YOUR_INSTANCE_ROOT_PASSWORD

1. Clone the repository and CD into the directory for this sample

        git clone https://github.com/GoogleCloudPlatform/php-docs-samples.git
        cd php-docs-samples/appengine/php72/cloudsql

1. Update `app.yaml` (or if you're using CloudSQL with PostgreSQL, update `app-postgres.yaml`)
   with your configuration values. These values are used when the application is deployed:

        env_variables:
            # Replace USER, PASSWORD, DATABASE, and CONNECTION_NAME with the
            # values obtained when configuring your Cloud SQL instance.
            CLOUDSQL_USER: USER
            CLOUDSQL_PASSWORD: PASSWORD
            CLOUDSQL_DSN: "mysql:dbname=DATABASE;unix_socket=/cloudsql/CONNECTION_NAME"

## Deploy to App Engine

**Prerequisites**

- Install the [Google Cloud SDK](https://developers.google.com/cloud/sdk/).

**Deploy with gcloud**

If you haven't already, authenticate gcloud using your Google account and
configure gcloud to use your project ID:

```sh
gcloud auth login
gcloud config set project YOUR_PROJECT_ID
```

Next, deploy your application:

```sh
gcloud app deploy
gcloud app browse
```

The last command will open `https://{YOUR_PROJECT_ID}.appspot.com/`
in your browser.

If your CloudSQL instance is running PostgreSQL instead of MySQL, deploy using
`app-postgres.yaml` instead:

```sh
gcloud app deploy app-postgres.yaml
```

## Run locally

To run the sample locally, you will want to use the [CloudSQL proxy](https://cloud.google.com/sql/docs/mysql/sql-proxy#install).
The CloudSQL proxy allows you to connect to your CloudSQL instance locally without
having to set up firewall rules.

```sh
./cloud_sql_proxy \
    -instances YOUR_INSTANCE_NAME \
    -dir /cloudsql \
    -credentials /path/to/your_service_account_credentials.json
```

Then set your CloudSQL environment variables and run the PHP web server:

```sh
# set local connection parameters (but replace the uppercase words!)
export CLOUDSQL_USERNAME=USER
export CLOUDSQL_PASSWORD=PASSWORD
export CLOUDSQL_DSN="mysql:dbname=DATABASE;unix_socket=/cloudsql/CONNECTION_NAME"

php -S localhost:8080
```

Now you can view the app running at [http://localhost:8080](http://localhost:8080)
in your browser.
