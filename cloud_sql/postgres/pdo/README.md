# Connection to Cloud SQL - PostgreSQL

## Before you begin

1. Before you use this code sample, you need to have [Composer](https://getcomposer.org/) installed or downloaded into this folder. Download instructions can be found [here](https://getcomposer.org/download/).
2. Create a PostgreSQL Cloud SQL Instance by following these [instructions](https://cloud.google.com/sql/docs/postgres/create-instance). Note the connection string, database user, and database password that you create.
3. Create a database for your application by following these [instructions](https://cloud.google.com/sql/docs/postgres/create-manage-databases). Note the database name.
4. Create a service account with the 'Cloud SQL Client' permissions by following these [instructions](https://cloud.google.com/sql/docs/postgres/connect-external-app#4_if_required_by_your_authentication_method_create_a_service_account). Download a JSON key to use to authenticate your connection.
5. Use the information noted in the previous steps:

```bash
export GOOGLE_APPLICATION_CREDENTIALS=/path/to/service/account/key.json
export CLOUD_SQL_CONNECTION_NAME='<MY-PROJECT>:<INSTANCE-REGION>:<MY-DATABASE>'
export DB_USER='my-db-user'
export DB_PASS='my-db-pass'
export DB_NAME='my-db-name'
export DB_HOSTNAME='localhost' # If connecting using TCP instead of Unix Sockets
```

Note: Saving credentials in environment variables is convenient, but not secure - consider a more secure solution such as [Cloud KMS](https://cloud.google.com/kms/) to help keep secrets safe.

## Running Locally

To run this application locally, download and install the `cloud_sql_proxy` by following the instructions [here](https://cloud.google.com/sql/docs/postgres/sql-proxy#install).

Once the proxy is ready, use the following command to start the proxy in the background:

```bash
$ ./cloud_sql_proxy -dir=/cloudsql --instances=$CLOUD_SQL_CONNECTION_NAME --credential_file=$GOOGLE_APPLICATION_CREDENTIALS
```

Note: Make sure to run the command under a user with write access in the `/cloudsql` directory. This proxy will use this folder to create a unix socket the application will use to connect to Cloud SQL.

Execute the following:

```bash
$ php -S localhost:8080
```

Navigate towards http://localhost:8080 to verify your application is running correctly.

## Google App Engine Standard

To run on GAE-Standard, create an App Engine project by following the setup for these [instructions](https://cloud.google.com/appengine/docs/standard/php7/quickstart#before-you-begin).

First, update `app.yaml` with the correct values to pass the environment variables into the runtime.

Next, the following command will deploy the application to your Google Cloud project:

```bash
$ gcloud app deploy
```
