# Google Cloud KMS API Samples

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.svg
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=kms

## Description

These samples show how to use the
[Google Cloud KMS API](https://cloud.google.com/kms/docs/reference/libraries) from PHP.

## Build and Run
1.  **Enable APIs** - [Enable the KMS API](https://console.cloud.google.com/flows/enableapi?apiid=cloudkms.googleapis.com)
    and create a new project or select an existing project.
2.  **Activate your Credentials** - If you do not already have an active set of credentials, create and download a [JSON Service Account key](https://console.cloud.google.com/apis/credentials/serviceaccountkey). Set the environment variable `GOOGLE_APPLICATION_CREDENTIALS` as the path to the downloaded JSON file.
3.  **Clone the repo** and cd into this directory

    ```
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd php-docs-samples/kms
    ```
4.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).
5.  Run `php src/SNIPPET_NAME.php`. The usage will print for each if no arguments
    are provided:
    ```sh
    $ php src/create_dataset.php
    Usage: php src/list_keyrings.php PROJECT_ID LOCATION

    $ php src/list_keyrings.php your-project-id us-west1
    Name: projects/your-project-id/locations/us-west1/keyRings/your-test-keyring
    Create Time: 2018-12-28 06:27:56
    ```

## Contributing changes

* See [CONTRIBUTING.md](../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../LICENSE)
