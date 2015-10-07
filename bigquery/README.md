# Google BigQuery PHP Sample Application

## Description

This simple command-line application demonstrates how to invoke Google BigQuery from PHP.

## Build and Run
1.  In the [Google Developers Console](https://console.developers.google.com/),
    create a new project or choose an existing project.
2.  In the [Google Developers Console](https://console.developers.google.com/),
    click **APIs & auth**, then click APIs.  Wait for a list of APIs to
    appear, then click BigQuery.  If BigQuery is not already enabled,
    click the Enable API button.
3.  In the [Google Developers Console](https://console.developers.google.com/),
    under **APIs & auth**, click Credentials.  Click the button to "Generate
    a new JSON key."  Set the environment variable
    `GOOGLE_APPLICATION_CREDENTIALS` to the path of the JSON key that was
    downloaded.
3.  Clone this repo with

    ```sh
    git clone https://github.com/GoogleCloudPlatform/php-docs-samples
```
4.  cd into the bigquery directory.
5.  Download [Composer](http://getcomposer.org/doc/00-intro.md) or update `composer self-update`.
6.  Run `php composer.phar install`.
7.  Run `php main.php`.

## Contributing changes

* See [CONTRIBUTING.md](../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../LICENSE)


