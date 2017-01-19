# Google Cloud KMS API Samples

## Description

These samples show how to use the [Google Cloud KMS API]
(https://cloud.google.com/kms/).

## Build and Run
1.  **Enable APIs** - [Enable the KMS API](https://console.cloud.google.com/flows/enableapi?apiid=cloudkms.googleapis.com)
    and create a new project or select an existing project.
2.  **Download The Credentials** - Click "Go to credentials" after enabling the APIs. Click "Create Credentials"
    and select "API key". Copy the API key.
3.  **Clone the repo** and cd into this directory
```
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd php-docs-samples/kms/api
```
4.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).
5.  **Run**:
```sh
$ php create_keyring.php
Usage: create_keyring.php [project_id] [name]
$ php create_cryptokey.php
Usage: create_cryptokey.php [project_id] [keyring] [name]
$ php encrypt.php
Usage: encrypt.php [key_name] [infile] [outfile]
$ php decrypt.php
Usage: decrypt.php [key_name] [infile] [outfile]
```

## Contributing changes

* See [CONTRIBUTING.md](../../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../../LICENSE)
