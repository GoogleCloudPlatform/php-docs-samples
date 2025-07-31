# Google Cloud Storage Control Samples

## Description

All code in the snippets directory demonstrate how to invoke
[Cloud Storage Control][google-cloud-php-storage-control] from PHP.

[cloud-storage-control]: https://cloud.google.com/storage/docs/access-control

## Setup:

1.  **Enable APIs** - [Enable the Storage Control Service API](https://console.cloud.google.com/flows/enableapi?apiid=storage.googleapis.com)
    and create a new project or select an existing project.
2.  **Download The Credentials** - Click "Go to credentials" after enabling the APIs. Click "New Credentials"
    and select "Service Account Key". Create a new service account, use the JSON key type, and
    select "Create". Once downloaded, set the environment variable `GOOGLE_APPLICATION_CREDENTIALS`
    to the path of the JSON key that was downloaded.
3.  **Clone the repo** and cd into this directory

    ```sh
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd php-docs-samples/storagecontrol
    ```
4.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).


## Samples

To run the Storage Control Quickstart Samples, run any of the files in `src/` on the CLI:

```
$ php src/quickstart.php

Usage: quickstart.php $bucketName

  @param string $bucketName The Storage bucket name
```

Above command returns the storage layout configuration for a given bucket.

## The client library

This sample uses the [Cloud Storage Control Client Library for PHP][google-cloud-php-storage-control].
You can read the documentation for more details on API usage and use GitHub
to [browse the source][google-cloud-php-source] and  [report issues][google-cloud-php-issues].

[google-cloud-php-storage-control]: https://cloud.google.com/storage/docs/reference/rpc
[google-cloud-php-source]: https://github.com/GoogleCloudPlatform/google-cloud-php
[google-cloud-php-issues]: https://github.com/GoogleCloudPlatform/google-cloud-php/issues
[google-cloud-sdk]: https://cloud.google.com/sdk/

## Contributing changes

* See [CONTRIBUTING.md](../../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../../LICENSE)

## Note

Tests for Anywhere cache Sample are not included due to the long operation times typical for cache operations.
