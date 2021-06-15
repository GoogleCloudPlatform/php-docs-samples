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
5.  To run the IOT Samples, run any of the files in `src/` on the CLI. Run them without arguments to print usage instructions:
```
$ php src/list_registries.php

Usage: list_registries.php $projectId [$location='us-central1']

    @param string $projectId Google Cloud project ID
    @param string $location (Optional) Google Cloud region
```

## Contributing changes

* See [CONTRIBUTING.md](../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../LICENSE)
