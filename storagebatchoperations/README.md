# Google Cloud Storage Batch Operations Samples

## Description

All code in the snippets directory demonstrates how to invoke
[Cloud Storage Batch Operations][google-cloud-php-storage-batch-operations] from PHP.

[cloud-storage-batch-operations]: https://cloud.google.com/storage/docs/batch-operations/overview

## Setup:

1.  **Enable APIs** - [Enable the Storage Batch Operations Service API](https://console.cloud.google.com/flows/enableapi?apiid=storage.googleapis.com)
    and create a new project or select an existing project.
2.  **Download The Credentials** - Click "Go to credentials" after enabling the APIs. Click "New Credentials"
    and select "Service Account Key". Create a new service account, use the JSON key type, and
    select "Create". Once downloaded, set the environment variable `GOOGLE_APPLICATION_CREDENTIALS`
    to the path of the JSON key that was downloaded.
3.  **Clone the repo** and cd into this directory

    ```sh
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd php-docs-samples/storagebatchoperations
    ```
4.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).


## Samples

To run the Storage Batch Operations Samples, run any of the files in `src/` on the CLI:

```
$ php src/create_job.php

Usage: create_job.php $jobId $bucketName $objectPrefix

  @param string $projectId The Project ID
  @param string $jobId The new Job ID
  @param string $bucketName The Storage bucket name
  @param string $objectPrefix The Object prefix
```

## The client library

This sample uses the [Cloud Storage Batch Operations Client Library for PHP][google-cloud-php-storage-batch-operations].
You can read the documentation for more details on API usage and use GitHub
to [browse the source][google-cloud-php-source] and  [report issues][google-cloud-php-issues].

[google-cloud-php-storage-batch-operations]: https://cloud.google.com/php/docs/reference/cloud-storagebatchoperations/latest
[google-cloud-php-source]: https://github.com/GoogleCloudPlatform/google-cloud-php
[google-cloud-php-issues]: https://github.com/GoogleCloudPlatform/google-cloud-php/issues
[google-cloud-sdk]: https://cloud.google.com/sdk/

## Contributing changes

* See [CONTRIBUTING.md](../../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../../LICENSE)
