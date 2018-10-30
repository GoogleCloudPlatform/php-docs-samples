# Google BigTable Table Admin Sample

## Description

All code in the `snippets` directory demonstrates how to connect to Cloud Bigtable and run some basic operations to create tables, create column families, update column families, delete column families and delete tables.

## Build and Run
1.  **Enable APIs** - [Enable the BigTable API](https://console.cloud.google.com/flows/enableapi?apiid=bigtable)
    and create a new project or select an existing project.
2.  **Download The Credentials** - Click "Go to credentials" after enabling the APIs. Click "New Credentials"
    and select "Service Account Key". Create a new service account, use the JSON key type, and
    select "Create". Once downloaded, set the environment variable `GOOGLE_APPLICATION_CREDENTIALS`
    to the path of the JSON key that was downloaded.
3.  **Clone the repo** and cd into this directory
    ```sh
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd php-docs-samples/bigtable/api/tableadmin
    ```

4.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).
5.  Run `php SNIPPET_NAME.php`. The usage will print for each if no arguments
    are provided:
    ```sh
    $ php main.php
    Usage: php main.php PROJECT_ID INSTANCE_ID TABLE_ID

    $ php main.php your-project-id your-instance-id your-table-id
    ```

## Contributing changes

* See [CONTRIBUTING.md](../../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../../LICENSE)
