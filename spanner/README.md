Google Cloud Spanner PHP Samples
================================

This directory contains samples for Google Cloud Spanner.
[Google Cloud Spanner][spanner] is a highly scalable, transactional, managed,
[NewSQL][newsql] database service. Cloud Spanner solves the need for a
horizontally-scaling database with consistent global transactions and SQL
semantics.

[spanner]: https://cloud.google.com/spanner/docs
[newsql]: https://en.wikipedia.org/wiki/NewSQL

## Setup

### Authentication

Authentication is typically done through [Application Default Credentials][adc]
which means you do not have to change the code to authenticate as long as
your environment has credentials. You have a few options for setting up
authentication:

1. When running locally, use the [Google Cloud SDK][google-cloud-sdk]

        gcloud auth application-default login

1. When running on App Engine or Compute Engine, credentials are already
   set-up. However, you may need to configure your Compute Engine instance
   with [additional scopes][additional_scopes].

1. You can create a [Service Account key file][service_account_key_file]. This file can be used to
   authenticate to Google Cloud Platform services from any environment. To use
   the file, set the ``GOOGLE_APPLICATION_CREDENTIALS`` environment variable to
   the path to the key file, for example:

        export GOOGLE_APPLICATION_CREDENTIALS=/path/to/service_account.json

[adc]: https://cloud.google.com/docs/authentication#getting_credentials_for_server-centric_flow
[additional_scopes]: https://cloud.google.com/compute/docs/authentication#using
[service_account_key_file]: https://developers.google.com/identity/protocols/OAuth2ServiceAccount#creatinganaccount

## Install Dependencies

1. Ensure the [gRPC PHP Extension][php_grpc] is installed and enabled on your machine.
1. [Enable the Cloud Spanner API](https://console.cloud.google.com/flows/enableapi?apiid=spanner.googleapis.com).

1. **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).

1. Create a service account at the
[Service account section in the Cloud Console](https://console.cloud.google.com/iam-admin/serviceaccounts/)

1. Download the json key file of the service account.

1. Set `GOOGLE_APPLICATION_CREDENTIALS` environment variable pointing to that file.

## Samples

To run the Spanner Samples:

    $ php spanner.php

    Cloud Spanner

    Usage:
      command [options] [arguments]

    Options:
      -h, --help            Display this help message
      -q, --quiet           Do not output any message
      -V, --version         Display this application version
          --ansi            Force ANSI output
          --no-ansi         Disable ANSI output
      -n, --no-interaction  Do not ask any interactive question
      -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

    Available commands:
      add-column                    Adds a new column to the Albums table in the example database.
      create-database               Creates a database and tables for sample data.
      create-index                  Adds a simple index to the example database.
      create-storing-index          Adds an storing index to the example database.
      help                          Displays help for a command
      insert-data                   Inserts sample data into the given database.
      list                          Lists commands
      query-data                    Queries sample data from the database using SQL.
      query-data-with-index         Queries sample data from the database using SQL and an index.
      query-data-with-new-column    Queries sample data from the database using SQL.
      read-data                     Reads sample data from the database.
      read-data-with-index          Reads sample data from the database using an index.
      read-data-with-storing-index  Reads sample data from the database using an index with a storing clause.
      read-only-transaction         Reads data inside of a read-only transaction.
      read-write-transaction        Performs a read-write transaction to update two sample records in the database.
      update-data                   Updates sample data in the database.


## Troubleshooting

If you get the following error, set the environment variable `GCLOUD_PROJECT` to your project ID:

```
[Google\Cloud\Core\Exception\GoogleException]
No project ID was provided, and we were unable to detect a default project ID.
```

## The client library

This sample uses the [Google Cloud Client Library for PHP][google-cloud-php].
You can read the documentation for more details on API usage and use GitHub
to [browse the source][google-cloud-php-source] and  [report issues][google-cloud-php-issues].

[php_grpc]: http://cloud.google.com/php/grpc
[google-cloud-php]: https://googlecloudplatform.github.io/google-cloud-php
[google-cloud-php-source]: https://github.com/GoogleCloudPlatform/google-cloud-php
[google-cloud-php-issues]: https://github.com/GoogleCloudPlatform/google-cloud-php/issues
[google-cloud-sdk]: https://cloud.google.com/sdk/
