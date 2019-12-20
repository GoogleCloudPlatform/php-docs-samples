# Google BigQuery PHP Sample Application

## Description

All code in the `src` directory demonstrate how to invoke
[Google BigQuery][bigquery] from PHP.

[bigquery]: https://cloud.google.com/bigquery/docs/quickstarts/quickstart-client-libraries

## Build and Run
1.  **Enable APIs** - [Enable the BigQuery API](https://console.cloud.google.com/flows/enableapi?apiid=bigquery)
    and create a new project or select an existing project.
2.  **Download The Credentials** - Click "Go to credentials" after enabling the APIs. Click "New Credentials"
    and select "Service Account Key". Create a new service account, use the JSON key type, and
    select "Create". Once downloaded, set the environment variable `GOOGLE_APPLICATION_CREDENTIALS`
    to the path of the JSON key that was downloaded.
3.  **Clone the repo** and cd into this directory
    ```sh
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd php-docs-samples/bigquery/api
    ```

4.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).
5.  Run `php src/SNIPPET_NAME.php`. The usage will print for each if no arguments
    are provided:
    ```sh
    $ php src/create_dataset.php
    Usage: php src/create_dataset.php PROJECT_ID DATASET_ID

    $ php src/create_dataset.php your-project-id test_dataset_123
    Created dataset test_dataset_123
    ```

## Contributing changes

* See [CONTRIBUTING.md](../../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../../LICENSE)
