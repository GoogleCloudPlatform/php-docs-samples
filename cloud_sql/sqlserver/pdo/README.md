# Connection to Cloud SQL - SQL Server

## Before you begin

1. This code sample requires the `pdo_sqlsrv` extension to be installed and enabled. For more information, including getting started guides, refer to the [source repository](https://github.com/Microsoft/msphpsql).
2. Before you use this code sample, you need to have [Composer](https://getcomposer.org/) installed or downloaded into this folder. Download instructions can be found [here](https://getcomposer.org/download/). Once you've installed composer, use it to install required dependencies by running `composer install`.
3. Create a SQL Server Cloud SQL Instance by following these [instructions](https://cloud.google.com/sql/docs/sqlserver/create-instance). Note the connection string, database user, and database password that you create.
4. Create a database for your application by following these [instructions](https://cloud.google.com/sql/docs/sqlserver/create-manage-databases). Note the database name.
5. Create a service account with the 'Cloud SQL Client' permissions by following these [instructions](https://cloud.google.com/sql/docs/postgres/connect-external-app#4_if_required_by_your_authentication_method_create_a_service_account). Download a JSON key to use to authenticate your connection.

## Running Locally

To run this application locally, download and install the `cloud_sql_proxy` by following the instructions [here](https://cloud.google.com/sql/docs/sqlserver/sql-proxy#install).

To authenticate with Cloud SQL, set the `$GOOGLE_APPLICATION_CREDENTIALS` environment variable:

```bash
export GOOGLE_APPLICATION_CREDENTIALS=/path/to/service/account/key.json
```

To run the Cloud SQL proxy, you need to set the instance connection name. See the instructions [here](https://cloud.google.com/sql/docs/sqlserver/quickstart-proxy-test#get_the_instance_connection_name) for finding the instance connection name.

```bash
export CLOUD_SQL_CONNECTION_NAME='<MY-PROJECT>:<INSTANCE-REGION>:<INSTANCE-NAME>'
```

Once the proxy is ready, use one of the following commands to start the proxy in the background.

You may connect to your instance via TCP. To connect via TCP, you must provide a port as part of the instance name, as demonstrated below.

```bash
$ ./cloud_sql_proxy \
    --instances=$CLOUD_SQL_CONNECTION_NAME=tcp:1433 \
    --credential_file=$GOOGLE_APPLICATION_CREDENTIALS
```

### Set Configuration Values

Set the required environment variables for your connection to Cloud SQL.

```bash
export DB_USER='my-db-user'
export DB_PASS='my-db-pass'
export DB_NAME='my-db-name'
export DB_HOSTNAME='127.0.0.1'
```

Note: Saving credentials in environment variables is convenient, but not secure - consider a more secure solution such as [Secret Manager](https://cloud.google.com/secret-manager/) to help keep secrets safe.

Execute the following:

```bash
$ php -S localhost:8080
```

Navigate towards http://localhost:8080 to verify your application is running correctly.

## Google App Engine Flex

To run on App Engine Flex, create an App Engine project by following the setup for these [instructions](https://cloud.google.com/appengine/docs/standard/php7/quickstart#before-you-begin).

First, update `app.yaml` with the correct values to pass the environment variables into the runtime.

In order to use the `sqlsrv` extension, you will need to build a [custom runtime](https://cloud.google.com/appengine/docs/flexible/custom-runtimes/quickstart). The `Dockerfile` in this sample contains a simple example of a custom PHP 7.2 runtime based off of the default App Engine Flex image with the `pdo_sqlsrv` extension installed.

Then, make sure that the service account `service-{PROJECT_NUMBER}>@gae-api-prod.google.com.iam.gserviceaccount.com` has the IAM role `Cloud SQL Client`.

Next, the following command will deploy the application to your Google Cloud project:

```bash
$ gcloud beta app deploy
```
