Google Cloud Bigtable PHP Quickstart Samples
=================================

[![image](https://gstatic.com/cloudssh/images/open-btn.png)](https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/GoogleCloudPlatform/php-docs-samples&page=editor&open_in_editor=bigtable/api/helloworld/main.php,bigtable/api/helloworld/README.md)

This directory contains samples for Google Cloud Bigtable. [Google Cloud
Bigtable](https://cloud.google.com/bigtable/docs) is Google's NoSQL Big
Data database service. It's the same database that powers many core
Google services, including Search, Analytics, Maps, and Gmail.

Setup
-----

### Authentication

This sample requires you to have authentication setup. Refer to the
[Authentication Getting Started
Guide](https://cloud.google.com/docs/authentication/getting-started) for
instructions on setting up credentials for applications.

### Install Dependencies

1.  Clone php-docs-samples and change directory to the sample directory
    you want to use.

    > ``` {.sourceCode .bash}
    > $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples.git
    > ```

2.  Install [composer](https://getcomposer.org/) if you do not already
    have them.
3.  Install the dependencies needed to run the samples.

    > ``` {.sourceCode .bash}
    > $ composer install
    > ```

Samples
-------

### Basic example

[![image](https://gstatic.com/cloudssh/images/open-btn.png)](https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/GoogleCloudPlatform/php-docs-samples&page=editor&open_in_editor=bigtable/api/quickstart/main.php,bigtable/api/quickstart/README.md)

To run this sample:

``` {.sourceCode .bash}
$ php main.php

usage: main.php

Demonstrates how to connect to Cloud Bigtable and run some basic operations
to create tables, create column families, update column families, delete column families and delete tables.
Prerequisites: - Create a Cloud Bigtable cluster.
https://cloud.google.com/bigtable/docs/creating-cluster - Set your Google
Application Default Credentials.
https://developers.google.com/identity/protocols/application-default-
credentials
```

The client library
------------------

This sample uses the [Google Cloud Client Library for
PHP](https://googleapis.github.io/google-cloud-php/). You can read the
documentation for more details on API usage and use GitHub to [browse
the source](https://github.com/googleapis/google-cloud-php) and
report issues.
