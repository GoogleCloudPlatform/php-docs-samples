# Google DLP PHP Sample Application

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.svg
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=dlp

## Description

This simple command-line application demonstrates how to invoke
[Google DLP API][dlp-api] from PHP.

[dlp-api]: https://cloud.google.com/dlp/docs/libraries

## Build and Run
1.  **Enable APIs** - [Enable the DLP API](
    https://console.cloud.google.com/flows/enableapi?apiid=dlp.googleapis.com)
    and create a new project or select an existing project.
2.  **Download The Credentials** - Click "Go to credentials" after enabling the APIs. Click
    "New Credentials"
    and select "Service Account Key". Create a new service account, use the JSON key type, and
    select "Create". Once downloaded, set the environment variable `GOOGLE_APPLICATION_CREDENTIALS`
    to the path of the JSON key that was downloaded.
3.  **Clone the repo** and cd into this directory
```
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd php-docs-samples/dlp
```
4.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).
5.  Execute the snippets in the [src/](src/) directory by running
    `php src/SNIPPET_NAME.php`. The usage will print for each if no arguments
    are provided:
    ```sh
    $ php src/inspect_string.php
    Usage: php src/inspect_string.php PROJECT_ID STRING

    $ php src/inspect_string.php your-project-id 'bob@example.com'
    Findings:
      Quote: bob@example.com
      Info type: EMAIL_ADDRESS
      Likelihood: LIKELY
    ```

See the [DLP Documentation](https://cloud.google.com/dlp/docs/inspecting-text) for more information.

## Contributing changes

* See [CONTRIBUTING.md](../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../LICENSE)
