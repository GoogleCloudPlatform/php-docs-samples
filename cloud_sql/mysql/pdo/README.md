# Connection to Cloud SQL - MySQL

## Before you begin

1. Before you use this code sample, you need to have [Composer](https://getcomposer.org/) installed or downloaded into this folder. Download instructions can be found [here](https://getcomposer.org/download/). Once you've installed composer, use it to install required dependencies by running `composer install`.
2. Create a MySQL Cloud SQL Instance by following these [instructions](https://cloud.google.com/sql/docs/mysql/create-instance). Note the connection string, database user, and database password that you create.
3. Create a database for your application by following these [instructions](https://cloud.google.com/sql/docs/mysql/create-manage-databases). Note the database name.
4. Create a service account with the 'Cloud SQL Client' permissions by following these [instructions](https://cloud.google.com/sql/docs/mysql/connect-external-app#4_if_required_by_your_authentication_method_create_a_service_account). Download a JSON key to use to authenticate your connection.

## Running Locally

To run this application locally, download and install the `cloud_sql_proxy` by following the instructions [here](https://cloud.google.com/sql/docs/mysql/sql-proxy#install).

To authenticate with Cloud SQL, set the `$GOOGLE_APPLICATION_CREDENTIALS` environment variable:

```bash
export GOOGLE_APPLICATION_CREDENTIALS=/path/to/service/account/key.json
```

To run the Cloud SQL proxy, you need to set the instance connection name. See the instructions [here](https://cloud.google.com/sql/docs/mysql/quickstart-proxy-test#get_the_instance_connection_name) for finding the instance connection name.

```bash
export CLOUD_SQL_CONNECTION_NAME='<MY-PROJECT>:<INSTANCE-REGION>:<INSTANCE-NAME>'
```

Once the proxy is ready, use one of the following commands to start the proxy in the background.

You may connect to your instance via either unix sockets or TCP. To connect using a socket, you must provide the `-dir` option when starting the proxy. To connect via TCP, you must provide a port as part of the instance name. Both are demonstrated below.

### Unix Socket mode

```bash
$ ./cloud_sql_proxy -dir=/cloudsql \
    --instances=$CLOUD_SQL_CONNECTION_NAME \
    --credential_file=$GOOGLE_APPLICATION_CREDENTIALS
```

Note: Make sure to run the command under a user with write access in the `/cloudsql` directory. This proxy will use this folder to create a unix socket the application will use to connect to Cloud SQL.

### TCP mode

```bash
$ ./cloud_sql_proxy \
    --instances=$CLOUD_SQL_CONNECTION_NAME=tcp:3306 \
    --credential_file=$GOOGLE_APPLICATION_CREDENTIALS
```

### Set Configuration Values
Set the required environment variables for your connection to Cloud SQL. If you are using TCP mode as described above, do not set the `CLOUD_SQL_CONNECTION_NAME` variable.

```bash
export DB_USER='my-db-user'
export DB_PASS='my-db-pass'
export DB_NAME='my-db-name'
export DB_HOSTNAME='localhost'
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

Then, make sure that the service account `service-{PROJECT_NUMBER}>@gae-api-prod.google.com.iam.gserviceaccount.com` has the IAM role `Cloud SQL Client`.

Next, the following command will deploy the application to your Google Cloud project:

```bash
$ gcloud beta app deploy
```

## Google App Engine Standard

To run on GAE-Standard, create an App Engine project by following the setup for these [instructions](https://cloud.google.com/appengine/docs/standard/php7/quickstart#before-you-begin).

First, update `app.yaml` with the correct values to pass the environment variables into the runtime.

Next, the following command will deploy the application to your Google Cloud project:

```bash
$ gcloud app deploy app-standard.yaml
```
