# Google Cloud KMS API Samples

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.png
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=kms

## Description

These samples show how to use the [Google Cloud KMS API]
(https://cloud.google.com/kms/).

## Build and Run
1.  **Enable APIs** - [Enable the KMS API](https://console.cloud.google.com/flows/enableapi?apiid=cloudkms.googleapis.com)
    and create a new project or select an existing project.
2.  **Activate your Credentials** - If you do not already have an active set of credentials, create and download a [JSON Service Account key](https://pantheon.corp.google.com/apis/credentials/serviceaccountkey). Set the environment variable `GOOGLE_APPLICATION_CREDENTIALS` as the path to the downloaded JSON file.
4.  **Clone the repo** and cd into this directory

    ```
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd php-docs-samples/kms
```
5.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).
6.  Run `php kms.php`. The following commands are available:

    ```sh
    encryption    Manage encryption for KMS
    iam           Manage IAM for KMS
    key           Manage keys for KMS
    keyring       Manage keyrings for KMS
    version       Manage key versions for KMS
```
7. Run `php kms.php COMMAND --help` to print information about the usage of each command.

## Contributing changes

* See [CONTRIBUTING.md](../../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../../LICENSE)
