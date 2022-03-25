# Google Transcoder PHP Sample Application

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.svg
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=media/transcoder

## Description

This simple command-line application demonstrates how to invoke
[Google Transcoder API][transcoder-api] from PHP.

[transcoder-api]: https://cloud.google.com/transcoder/docs/reference/libraries

## Build and Run
1.  **Enable APIs** - [Enable the Transcoder API](
    https://console.cloud.google.com/flows/enableapi?apiid=transcoder.googleapis.com)
    and create a new project or select an existing project.
2.  **Download The Credentials** - Click "Go to credentials" after enabling the APIs. Click
    "New Credentials"
    and select "Service Account Key". Create a new service account, use the JSON key type, and
    select "Create". Once downloaded, set the environment variable `GOOGLE_APPLICATION_CREDENTIALS`
    to the path of the JSON key that was downloaded.
3.  **Clone the repo** and cd into this directory
```
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd media/transcoder
```
4.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).
5.  Execute the snippets in the [src/](src/) directory by running
    `php src/SNIPPET_NAME.php`. The usage will print for each if no arguments
    are provided:
    ```sh
    $ php php src/create_job_from_ad_hoc.php
    Usage: create_job_from_ad_hoc.php $projectId $location $inputUri $outputUri

      @param string $projectId The ID of your Google Cloud Platform project.
      @param string $location The location of the job.
      @param string $inputUri Uri of the video in the Cloud Storage bucket.
      @param string $outputUri Uri of the video output folder in the Cloud Storage bucket.


    $ php create_job_from_ad_hoc.php my-project us-central1 gs://my-bucket/input.mp4 gs://my-bucket/adhoc/
    Job: projects/657323817858/locations/us-central1/jobs/13beaa6b-5a33-4a86-b280-04b524546291
    ```

See the [Transcoder Documentation](https://cloud.google.com/transcoder/docs/) for more information.

## Troubleshooting

### bcmath extension missing

If you see an error like this:

```
PHP Fatal error:  Uncaught Error: Call to undefined function Google\Protobuf\Internal\bcsub()
```

You need to install the BC-Math extension.

See the [Troubleshooting guide](https://cloud.google.com/transcoder/docs/troubleshooting) for more information.

## Contributing changes

* See [CONTRIBUTING.md](../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../LICENSE)
