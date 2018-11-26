# Connection to Cloud SQL - PostgreSQL

## Setup

1. Clone this repository
2. Set up the Environment Variables:

```bash
export GOOGLE_APPLICATION_CREDENTIALS=/path/to/service/account/key.json
export CLOUD_SQL_CONNECTION_NAME='<MY-PROJECT>:<INSTANCE-REGION>:<MY-DATABASE>'
export DB_USERNAME='my-db-username'
export DB_PASSWORD='my-db-password'
export DB_NAME='my-db-name'
export DB_HOSTNAME='localhost' # If connecting using cloud_sql_proxy
```

3. Install the dependencies using Composer

```bash
$ composer install
```
OR

```bash
$ php composer.phar install
```

## Running Locally

1. Execute the following:

```
$ php -S localhost:3000 run.php
```
