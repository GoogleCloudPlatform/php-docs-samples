# Connection to Cloud SQL - PostgreSQL

## Before you begin

1. Before you use this code sample, you need to have
[Composer](https://getcomposer.org/) installed or downloaded into this folder.
Download instructions can be found [here](https://getcomposer.org/download/).
Once you've installed composer, use it to install required dependencies by
running `composer install`.
2. Create a PostgreSQL Cloud SQL Instance by following these
[instructions](https://cloud.google.com/sql/docs/postgres/create-instance). Note
the connection string, database user, and database password that you create.
3. Create a database for your application by following these
[instructions](https://cloud.google.com/sql/docs/postgres/create-manage-databases).
Note the database name.
4. Create a service account with the 'Cloud SQL Client' permissions by following
these
[instructions](https://cloud.google.com/sql/docs/postgres/connect-external-app#4_if_required_by_your_authentication_method_create_a_service_account).
Download a JSON key to use to authenticate your connection.

## Running Locally

To run this application locally, download and install the `cloud_sql_proxy` by
following the instructions
[here](https://cloud.google.com/sql/docs/postgres/sql-proxy#install).

Instructions are provided below for using the proxy with a TCP connection or a
Unix domain socket. On Linux or macOS, you can use either option, but the
Windows proxy requires a TCP connection.

### Launch proxy with Unix Domain Socket

NOTE: this option is currently only supported on Linux and macOS. Windows users
should use the TCP option.

To use a Unix socket, you'll need to create a directory and give access to the
user running the proxy:

```bash
sudo mkdir /cloudsql
sudo chown -R $USER /cloudsql
```

Use these terminal commands to initialize environment variables:

```bash
export GOOGLE_APPLICATION_CREDENTIALS=/path/to/service/account/key.json
export INSTANCE_CONNECTION_NAME='<PROJECT-ID>:<INSTANCE-REGION>:<INSTANCE-NAME>'
export INSTANCE_UNIX_SOCKET='/cloudsql/<PROJECT-ID>:<INSTANCE-REGION>:<INSTANCE-NAME>'
export DB_USER='<DB_USER_NAME>'
export DB_PASS='<DB_PASSWORD>'
export DB_NAME='<DB_NAME>'
```

Note: Saving credentials in environment variables is convenient, but not
secure - consider a more secure solution such as
[Secret Manager](https://cloud.google.com/secret-manager/) to help keep secrets
safe.

Then use the following command to launch the proxy in the background:

```bash
./cloud_sql_proxy -dir=/cloudsql --instances=$INSTANCE_CONNECTION_NAME --credential_file=$GOOGLE_APPLICATION_CREDENTIALS &
```

### Launch proxy with TCP

To run the sample locally with a TCP connection, set environment variables and
launch the proxy as shown below.

#### Linux / Mac OS

Use these terminal commands to initialize environment variables:

```bash
export GOOGLE_APPLICATION_CREDENTIALS=/path/to/service/account/key.json
export INSTANCE_CONNECTION_NAME='<PROJECT-ID>:<INSTANCE-REGION>:<INSTANCE-NAME>'
export INSTANCE_HOST='127.0.0.1'
export DB_USER='<DB_USER_NAME>'
export DB_PASS='<DB_PASSWORD>'
export DB_NAME='<DB_NAME>'
```

Note: Saving credentials in environment variables is convenient, but not
secure - consider a more secure solution such as
[Secret Manager](https://cloud.google.com/secret-manager/) to help keep secrets
safe.

Then use the following command to launch the proxy in the background:

```bash
./cloud_sql_proxy -instances=$INSTANCE_CONNECTION_NAME=tcp:5432 -credential_file=$GOOGLE_APPLICATION_CREDENTIALS &
```

#### Windows/PowerShell

Use these PowerShell commands to initialize environment variables:

```bash
$env:GOOGLE_APPLICATION_CREDENTIALS="<CREDENTIALS_JSON_FILE>"
$env:INSTANCE_HOST="127.0.0.1"
$env:DB_USER="<DB_USER_NAME>"
$env:DB_PASS="<DB_PASSWORD>"
$env:DB_NAME="<DB_NAME>
```

Note: Saving credentials in environment variables is convenient, but not
secure - consider a more secure solution such as
[Secret Manager](https://cloud.google.com/secret-manager/) to help keep secrets
safe.

Then use the following command to launch the proxy in a separate PowerShell
session:

```powershell
Start-Process -filepath "C:\<path to proxy exe>" -ArgumentList "-instances=<project-id>:<region>:<instance-name>=tcp:5432 -credential_file=<CREDENTIALS_JSON_FILE>"
```

### Testing the application

Execute the following to start the application server:

```bash
$ php -S localhost:8080
```

Navigate towards http://localhost:8080 to verify your application is running
correctly.

## Google App Engine Standard
Note: App Engine Standard does not support TCP connections to Cloud SQL
instances, only Unix socket connections.

To run on App Engine Standard, create an App Engine project by following the
setup for these
[instructions](https://cloud.google.com/appengine/docs/standard/php7/quickstart#before-you-begin).

First, update [app.standard.yaml](app.standard.yaml) with the correct values to pass the
environment variables into the runtime.

Next, the following command will deploy the application to your Google Cloud
project:

```bash
$ gcloud app deploy app.standard.yaml
```

## Google App Engine Flex
To run on App Engine Flex, create an App Engine project by following the setup
for these
[instructions](https://cloud.google.com/appengine/docs/standard/php7/quickstart#before-you-begin).

First, update [app.flex.yaml](app.flex.yaml) with the correct values to pass the environment
variables into the runtime.

To use a TCP connection instead of a Unix socket to connect your sample to your
Cloud SQL instance on App Engine, make sure to uncomment the `INSTANCE_HOST`
field under `env_variables`. Also make sure to remove the uncommented
`beta_settings` and `cloud_sql_instances` fields and replace them with the
commented `beta_settings` and `cloud_sql_instances` fields.

Then, make sure that the App Engine default service account
`<PROJECT-ID>@appspot.gserviceaccount.com` has
the IAM role `Cloud SQL Client`.

Also, make sure that the Cloud Build service account
`cloudbuild@<PROJECT-ID>.iam.gserviceaccount.com` has
the IAM role `Cloud SQL Client`.

Next, the following command will deploy the application to your Google Cloud
project:

```bash
$ gcloud beta app deploy app.flex.yaml
```

