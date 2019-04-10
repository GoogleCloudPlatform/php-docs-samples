# Google IOT PHP Sample Application

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.svg
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=iot

## Description

This simple command-line application demonstrates how to invoke Google
IOT API from PHP. These samples are best seen in the context of the
[Official API Documentation](https://cloud.google.com/iot/docs).

## Build and Run
1.  **Enable APIs** - [Enable the IOT API](
    https://console.cloud.google.com/flows/enableapi?apiid=iot.googleapis.com)
    and create a new project or select an existing project.
2.  **Download The Credentials** - Click "Go to credentials" after enabling the APIs. Click
    "New Credentials"
    and select "Service Account Key". Create a new service account, use the JSON key type, and
    select "Create". Once downloaded, set the environment variable `GOOGLE_APPLICATION_CREDENTIALS`
    to the path of the JSON key that was downloaded.
3.  **Clone the repo** and cd into this directory
```
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd php-docs-samples/iot
```
4.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).
5.  Run `php iot.php`. The following commands are available:

    ```
    bind-device-to-gateway      (Beta feature) Bind a device to a gateway.
    create-es-device            Create a new device with the given id, using ES256 for authentication.
    create-gateway              (Beta feature) Create a new gateway with the given id.
    create-registry             Creates a registry and returns the result.
    create-rsa-device           Create a new device with the given id, using RS256 for authentication.
    create-unauth-device        Create a new device without authentication.
    delete-device               Delete the device with the given id.
    delete-gateway              (Beta feature) Delete the gateway with the given id.
    delete-registry             Deletes the specified registry.
    get-device                  Retrieve the device with the given id.
    get-device-configs          Lists versions of a device config in descending order (newest first).
    get-device-state            Retrieve a device's state blobs.
    get-iam-policy              Retrieves IAM permissions for the given registry.
    get-registry                Retrieves a device registry.
    help                        Displays help for a command
    list                        Lists commands
    list-devices                List all devices in the registry.
    list-devices-for-gateway    List devices for the given gateway.
    list-gateways               List gateways for the given registry.
    list-registries             List all registries in the project.
    patch-es-device             Patch device with ES256 public key.
    patch-rsa-device            Patch device with RSA256 certificate.
    send-command-to-device      Sends a command to a device.
    set-device-config           Set a device's configuration.
    set-device-state            Sets the state of a device.
    set-iam-policy              Sets IAM permissions for the given registry to a single role/member.
    unbind-device-from-gateway  (Beta feature) Unbind a device from a gateway.

    Example:

    ```
    $ php iot.php create-registry my-registry my-pubsub-topic
    Creating Registry
    Id: my-registry, Name: projects/my-project/locations/us-central1/registries/my-registry
    ```


6. Run `php iot.php COMMAND --help` to print information about the usage of each command.

## Contributing changes

* See [CONTRIBUTING.md](../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../LICENSE)
