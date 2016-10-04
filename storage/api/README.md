# Google Cloud Storage PHP Sample Application

## Description

This simple command-line application demonstrates how to invoke Google Cloud Storage from PHP.

## Licensing

* See [LICENSE](../../LICENSE)

## Build and Run
1.  **Enable APIs** - [Enable the Storage API](https://console.cloud.google.com/flows/enableapi?apiid=storage_api)
    and create a new project or select an existing project.
2.  **Download The Credentials** - Click "Go to credentials" after enabling the APIs. Click "New Credentials"
    and select "Service Account Key". Create a new service account, use the JSON key type, and
    select "Create". Once downloaded, set the environment variable `GOOGLE_APPLICATION_CREDENTIALS`
    to the path of the JSON key that was downloaded.
3.  **Clone the repo** and cd into this directory

    ```sh
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd php-docs-samples/storage/cli
```
4.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).
5.  Run `php storage.php`. The following commands are available:

    ```sh
    bucket-acl          Manage the ACL for Cloud Storage buckets.
    bucket-default-acl  Manage the default ACL for Cloud Storage buckets.
    buckets             Manage Cloud Storage buckets
    encryption          Upload and download Cloud Storage objects with encryption
    object-acl          Manage the ACL for Cloud Storage objects
    objects             Manage Cloud Storage objects
```
6. Run `php storage.php COMMAND --help` to print information about the usage of each command.

## Contributing changes

* See [CONTRIBUTING.md](../../CONTRIBUTING.md)
