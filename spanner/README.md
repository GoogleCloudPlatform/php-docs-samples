Google Cloud Spanner PHP Samples
================================

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.svg
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=spanner

This directory contains samples for calling [Google Cloud Spanner][spanner]
from PHP.

Google Cloud Spanner is a highly scalable, transactional, managed,
[NewSQL][newsql] database service. Cloud Spanner solves the need for a
horizontally-scaling database with consistent global transactions and SQL
semantics.

See [Getting Started in PHP][getting-started-php]
for a walkthrough of these samples.

[spanner]: https://cloud.google.com/spanner/docs/reference/libraries
[newsql]: https://en.wikipedia.org/wiki/NewSQL
[getting-started-php]: https://cloud.google.com/spanner/docs/getting-started/php/

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

### Create an instance

These samples require you to first set up a [Spanner Instance][create-instance].
Once you've finished with the samples, you can [delete your instance][delete-instance]
to prevent incuring any additional charges.

[create-instance]: https://cloud.google.com/spanner/docs/create-manage-instances
[delete-instance]: https://cloud.google.com/spanner/docs/create-manage-instances#delete-instance

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
      add-column                           Adds a new column to the Albums table in the example database.
      add-timestamp-column                 Adds a commit timestamp column to a table.
      batch-query-data                     Batch queries sample data from the database using SQL.
      create-database                      Creates a database and tables for sample data.
      create-index                         Adds a simple index to the example database.
      create-storing-index                 Adds an storing index to the example database.
      create-table-timestamp               Creates a table with a commit timestamp column.
      create-table-with-datatypes          Creates a table with supported datatypes.
      delete-data-with-dml                 Remove sample data from the given database with a DML statement.
      deleted-data-with-partitioned-dml    Deletes sample data in the database by partition with a DML statement.
      help                                 Displays help for a command
      insert-data                          Inserts sample data into the given database.
      insert-data-timestamp                Inserts data into a table with a commit timestamp column.
      insert-data-with-datatypes           Inserts data with supported datatypes.
      insert-data-with-dml                 Inserts sample data into the given database with a DML statement.
      insert-struct-data                   Inserts sample data that can be used to test STRUCT parameters in queries.
      list                                 Lists commands
      query-data                           Queries sample data from the database using SQL.
      query-data-timestamp                 Queries sample data from a database with a commit timestamp column.
      query-data-with-array-parameter      Queries sample data using SQL with an ARRAY parameter.
      query-data-with-array-of-struct      Queries sample data from the database with an array of struct.
      query-data-with-bool-parameter       Queries sample data using SQL with a BOOL parameter.
      query-data-with-bytes-parameter      Queries sample data using SQL with a BYTES parameter.
      query-data-with-date-parameter       Queries sample data using SQL with a DATE parameter.
      query-data-with-float-parameter      Queries sample data using SQL with a FLOAT64 parameter.
      query-data-with-index                Queries sample data from the database using SQL and an index.
      query-data-with-int-parameter        Queries sample data using SQL with a INT64 parameter.
      query-data-with-nested-struct-field  Queries sample data from the database with a nested struct field value.
      query-data-with-new-column           Queries sample data from the database using SQL.
      query-data-with-parameter            Query DML inserted sample data using SQL with a parameter.
      query-data-with-string-parameter     Queries sample data using SQL with a STRING parameter.
      query-data-with-struct               Queries sample data from the database with a struct.
      query-data-with-struct-field         Queries sample data from the database with a struct field value.
      query-data-with-timestamp-parameter  Queries sample data using SQL with a TIMESTAMP parameter.
      read-data                            Reads sample data from the database.
      read-data-with-index                 Reads sample data from the database using an index.
      read-data-with-storing-index         Reads sample data from the database using an index with a storing clause.
      read-only-transaction                Reads data inside of a read-only transaction.
      read-stale-data                      Reads sample data from the database, with a maximum staleness of 3 seconds.
      read-write-transaction               Performs a read-write transaction to update two sample records in the database.
      update-data                          Updates sample data in the database.
      update-data-timestamp                Updates sample data in a table with a commit timestamp column.
      update-data-with-batch-dml           Updates sample data in the given database using Batch DML.
      update-data-with-dml                 Updates sample data into the given database with a DML statement.
      update-data-with-dml-structs         Updates data using DML statement with structs.
      update-data-with-dml-timestamp       Update sample data from the given database with a DML statement and timestamp.
      update-data-with-partitioned-dml     Updates sample data in the database by partition with a DML statement.
      write-data-with-dml                  Writes sample data into the given database with a DML statement.
      write-data-with-dml-transaction      Performs a read-write transaction to update two sample records in the database.
      write-read-with-dml                  Writes then reads data inside a Transaction with a DML statement.


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
