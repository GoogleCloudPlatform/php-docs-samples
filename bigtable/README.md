# Google Bigtable Sample

## Description

These samples show how to use the
[Cloud Bigtable API][bigtable] from PHP.

All code in the `src` directory demonstrates how to connect to Cloud Bigtable and run some basic operations to create instance, create cluster, delete instance and delete cluster.

[bigtable]: https://cloud.google.com/bigtable/docs/reference/libraries

## Build and Run
1.  **Enable APIs** - [Enable the Bigtable API](https://console.cloud.google.com/flows/enableapi?apiid=bigtable)
    and create a new project or select an existing project.
2.  **Download The Credentials** - Click "Go to credentials" after enabling the APIs. Click "New Credentials"
    and select "Service Account Key". Create a new service account, use the JSON key type, and
    select "Create". Once downloaded, set the environment variable `GOOGLE_APPLICATION_CREDENTIALS`
    to the path of the JSON key that was downloaded.
3.  **Clone the repo** and cd into this directory
    ```sh
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd php-docs-samples/bigtable/api
    ```

4.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).
5.  Run `php SNIPPET_NAME.php`. The usage will print for each if no arguments
    are provided:
    ```sh
    $ php src/run_instance_operations.php
    Usage: php src/run_instance_operations.php PROJECT_ID INSTANCE_ID TABLE_ID

    $ php src/run_instance_operations.php your-project-id your-instance-id your-table-id
    ```

## Contributing changes

* See [CONTRIBUTING.md](../../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../../LICENSE)
