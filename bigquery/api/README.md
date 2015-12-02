# Google BigQuery PHP Sample Application

## Description

This simple command-line application demonstrates how to invoke Google BigQuery from PHP.

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
5.  Run `php main.php YOUR_PROJECT_NAME` where YOUR_PROJECT_NAME is the
    project associated with the credentials from **step 2**.

    ```sh
    $ php main.php my-project-name

    Query Results:
    ------------
    hamlet                        5318
    kinghenryv                    5104
    cymbeline                     4875
    troilusandcressida            4795
    kinglear                      4784
    kingrichardiii                4713
    2kinghenryvi                  4683
    coriolanus                    4653
    2kinghenryiv                  4605
    antonyandcleopatra            4582
```

## Contributing changes

* See [CONTRIBUTING.md](../../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../../LICENSE)


