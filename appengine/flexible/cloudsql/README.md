# Cloud SQL & Google App Engine Flexible Environment

This sample application demonstrates how to use [Cloud SQL with Google App Engine Flexible Environment](https://cloud.google.com/appengine/docs/flexible/php/using-cloud-sql).

## Setup

Before running this sample:

## Prerequisites

- Install [`composer`](https://getcomposer.org)
- Install dependencies by running:

```sh
composer install
```

## Setup

Before you can run or deploy the sample, you will need to do the following:

1. Create a [Second Generation Cloud SQL](https://cloud.google.com/sql/docs/create-instance) instance. You can do this from the [Cloud Console](https://console.developers.google.com) or via the [Cloud SDK](https://cloud.google.com/sdk). To create it via the SDK use the following command:

        $ gcloud beta sql instances create YOUR_INSTANCE_NAME --tier=db-f1-micro --activation-policy=ALWAYS

	> Note: the `--tier` option is required to create a `Second Generation` instance. See the
	  full list of available tiers by running `gcloud sql tiers list`

2. Set the root password on your Cloud SQL instance:

        $ gcloud sql instances set-root-password YOUR_INSTANCE_NAME --password YOUR_INSTANCE_ROOT_PASSWORD

3. Install and run the [CloudSQL Proxy](https://cloud.google.com/sql/docs/mysql-connect-proxy)

4. Create a database for this example

        $ mysql -h 127.0.0.1 -u root -p -e "CREATE DATABASE <YOUR_DATABASE_NAME>;"


## Deploy to App Engine

**Prerequisites**

- Install the [Google Cloud SDK](https://developers.google.com/cloud/sdk/).

**Deploy with gcloud**

1. Update `app.yaml` with the configuration values for `USER`, `PASSWORD`, and
   `DATABASE` with the values you used during setup.

1. Get the CloudSQL connection name

    $ gcloud beta sql instances describe YOUR_INSTANCE_NAME | grep connectionName

1. Update `app.yaml` with the configuration value for `CONNECTION_NAME` you retrieved
   at the end up setup.

```
gcloud config set project YOUR_PROJECT_ID
gcloud app deploy
gcloud app browse
```

The last command will open `https://{YOUR_PROJECT_ID}.appspot.com/`
in your browser.

## Run locally

1.  Ensure the [CloudSQL Proxy](https://cloud.google.com/sql/docs/external#proxy) is
    installed and running.

1.  Set the following environment variables with the configuration values for
    `USER`, `PASSWORD`, and `DATABASE` you used during setup:

    ```sh
    # set local mysql connection parameters
    export MYSQL_DSN="mysql:host=127.0.0.1;port=3306;dbname=DATABASE"
    export MYSQL_USERNAME=USER
    export MYSQL_PASSWORD=PASSWORD
    ```

1.  Run the application

    ```sh
    cd php-docs-samples/appengine/standard/cloudsql
    php -S localhost:8080
    ```

Now you can view the app running at [http://localhost:8080](http://localhost:8080)
in your browser.
