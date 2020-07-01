# Google Service Directory PHP Sample Application

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.svg
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=servicedirectory

## Description

This simple command-line application demonstrates how to invoke the
[Google Service Directory API][servicedirectory-api] from PHP.

[servicedirectory-api]: https://cloud.google.com/service-directory/

## Build and Run
1.  **Enable APIs** - [Enable the Service Directory API](
    https://console.cloud.google.com/flows/enableapi?apiid=servicedirectory.googleapis.com)
    and create a new project or select an existing project.
2.  **Download The Credentials** - Click "Go to credentials" after enabling the APIs. Click
    "New Credentials"
    and select "Service Account Key". Create a new service account, use the JSON key type, and
    select "Create". Once downloaded, set the environment variable `GOOGLE_APPLICATION_CREDENTIALS`
    to the path of the JSON key that was downloaded.
3.  **Clone the repo** and cd into this directory
```
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd php-docs-samples/servicedirectory
```
4.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).
5.  Execute the snippets in the [src/](src/) directory by running
    `php src/SNIPPET_NAME.php`. The usage will print for each if no arguments
    are provided:
    ```sh
    $ php src/quickstart.php
    Usage: php src/quickstart.php PROJECT_ID STRING

    $ php src/quickstart.php your-project-id us-east1
    Namespaces: projects/you-project-id/locations/us-east1/namespaces/your-namespace
    ```

See the [Service Directory Documentation](https://cloud.google.com/service-directory/docs/) for more information.

## Troubleshooting

### bcmath extension missing

If you see an error like this:

```
PHP Fatal error:  Uncaught Error: Call to undefined function Google\Protobuf\Internal\bccomp() in /usr/local/google/home/crwilson/github/GoogleCloudPlatform/php-docs-samples/dlp/vendor/google/protobuf/src/Google/Protobuf/Internal/Message.php:986
```

You may need to install the bcmath PHP extension.
e.g. (may depend on your php version)
```
$ sudo apt-get install php7.3-bcmath
```


## Contributing changes

* See [CONTRIBUTING.md](../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../LICENSE)
