# Google Secret Manager PHP Sample Application

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.svg
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=secretmanager

## Description

This simple command-line application demonstrates how to invoke
[Google Secret Manager][secretmanager] from PHP.

## Build and Run

1.  **Enable APIs** - [Enable the Secret Manager
    API](https://console.cloud.google.com/flows/enableapi?apiid=secretmanager.googleapis.com)
    and create a new project or select an existing project. To run the rotation tests, you will need to [Create a Pub/Sub topic](https://cloud.google.com/pubsub/docs/create-topic). CMEK related test cases need separate [KMS key](https://cloud.google.com/kms/docs/create-key) for global and regional tests.
    
    Set the following environment variables:

    - GOOGLE_CLOUD_PUBSUB_TOPIC - Full name of topic (projects/{project}/topics/{topic}).
    -  GOOGLE_CLOUD_KMS_KEY - Full name of global KMS key (projects/{project}/locations/global/keyRings/{keyring}/cryptoKeys/{key}).
    -  GOOGLE_CLOUD_REGIONAL_KMS_KEY - Full name of regional KMS key (projects/{project}/locations/{location}/keyRings/{keyring}/cryptoKeys/{key}).

1.  **Download The Credentials** - Click "Go to credentials" after enabling the
    APIs. Click "New Credentials" and select "Service Account Key". Create a new
    service account, use the JSON key type, and select "Create". Once
    downloaded, set the environment variable `GOOGLE_APPLICATION_CREDENTIALS` to
    the path of the JSON key that was downloaded.

1.  **Clone the repo** and cd into this directory

    ```text
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd php-docs-samples/secretmanager
    ```

1.  **Install dependencies** via [Composer][install-composer]. If composer is
    installed locally:


    ```text
    $ php composer.phar install
    ```

    If composer is installed globally:

    ```text
    $ composer install
    ```

1.  Execute the snippets in the [src/](src/) directory by running:

    ```text
    $ php src/SNIPPET_NAME.php
    ```

    The usage will print for each if no arguments are provided.

See the [Secret Manager Documentation](https://cloud.google.com/secret-manager/docs) for more information.

## Contributing changes

* See [CONTRIBUTING.md](../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../LICENSE)

[install-composer]: http://getcomposer.org/doc/00-intro.md
[secretmanager]: https://cloud.google.com/secret-manager
