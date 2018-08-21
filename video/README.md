# Google Video PHP Sample Application

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.svg
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=video

## Description

This simple command-line application demonstrates how to invoke Google
Video Intelligence API from PHP.

## Build and Run
1.  **Enable APIs** - [Enable the Video Intelligence API](
    https://console.cloud.google.com/flows/enableapi?apiid=videointelligence.googleapis.com)
    and create a new project or select an existing project.
2.  **Download The Credentials** - Click "Go to credentials" after enabling the APIs. Click
    "New Credentials"
    and select "Service Account Key". Create a new service account, use the JSON key type, and
    select "Create". Once downloaded, set the environment variable `GOOGLE_APPLICATION_CREDENTIALS`
    to the path of the JSON key that was downloaded.
3.  **Clone the repo** and cd into this directory
```
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd php-docs-samples/video
```
4.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).
5.  Run `php video.php`. The following commands are available:

    ```
    faces           Detect faces changes in video using the Video Intelligence API
    help            Displays help for a command
    labels          Detect labels in video using the Video Intelligence API
    labels-in-file  Detect labels in a file using the Video Intelligence API
    list            Lists commands
    safe-search     Detect safe search in video using the Video Intelligence API
    shots           Detect shots in video using the Video Intelligence API
    ```

    Example:

    ```
    $ php video.php shots gs://demomaker/cat.mp4
    0s to 14.833664s
    ```


6. Run `php video.php COMMAND --help` to print information about the usage of each command.

## Contributing changes

* See [CONTRIBUTING.md](../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../LICENSE)
