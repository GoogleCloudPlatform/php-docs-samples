# Google Cloud Video Stitcher PHP Sample Application

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.svg
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=media/videostitcher

## Description

This simple command-line application demonstrates how to invoke
[Cloud Video Stitcher API][videostitcher-api] from PHP.

[videostitcher-api]: https://cloud.google.com/video-stitcher/docs/reference/libraries

## Build and Run
1.  **Enable APIs** - [Enable the Video Stitcher API](
    https://console.cloud.google.com/flows/enableapi?apiid=videostitcher.googleapis.com)
    and create a new project or select an existing project.
2.  **Download The Credentials** - Click "Go to credentials" after enabling the APIs. Click
    "New Credentials"
    and select "Service Account Key". Create a new service account, use the JSON key type, and
    select "Create". Once downloaded, set the environment variable `GOOGLE_APPLICATION_CREDENTIALS`
    to the path of the JSON key that was downloaded.
3.  **Clone the repo** and cd into this directory
```
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd media/videostitcher
```
4.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).
5.  Execute the snippets in the [src/](src/) directory by running
    `php src/SNIPPET_NAME.php`. The usage will print for each if no arguments
    are provided:
    ```sh
    $ php src/create_slate.php
    Usage: create_slate.php $callingProjectId $location $slateId $slateUri

        @param string $callingProjectId     The project ID to run the API call under
        @param string $location             The location of the slate
        @param string $slateId              The name of the slate to be created
        @param string $slateUri             The public URI for an MP4 video with at least one audio track

    $ php create_slate.php my-project us-central1 my-slate https://storage.googleapis.com/my-bucket/my-slate.mp4
    Slate: projects/123456789012/locations/us-central1/slates/my-slate
    ```

See the [Video Stitcher Documentation](https://cloud.google.com/video-stitcher/docs/) for more information.

## Contributing changes

* See [CONTRIBUTING.md](../../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../../LICENSE)
