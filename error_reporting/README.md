# Stackdriver Error Reporting

`quickstart.php` is a simple command-line program to demonstrate logging an
exception to Stackdriver Error Reporting.

# Installation

1. To use this sample, you must first [enable the Stackdriver Error Reporting API][0]
1. Next, **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    1. Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).
    1. If the [gRPC PHP Extension][php_grpc] is not enabled for your version of PHP,
    install your dependencies with the `--ignore-platform-reqs` flag. **Note** this will
    only work for `quickstart.php`, as `error_reporting.php` requires gRPC.
    ```
    composer install --ignore-platform-reqs
    ```
1. Create a service account at the [Service account section in the Cloud Console][2]
1. Download the json key file of the service account.
1. Set `GOOGLE_APPLICATION_CREDENTIALS` environment variable pointing to that file.

# Running quickstart.php

Open `quickstart.php` in a text editor and replace the text `YOUR_PROJECT_ID`
with your Project ID.

Run the samples:

```sh
php quickstart.php
Exception logged to Stack Driver Error Reporting
```

View [Stackdriver Error Reporting][1] in the Cloud Console to see the logged
exception.

# Running error_reporting.php

For more granular control over your error reporting, see the examples in `error_reporting.php`
Follow the instructions to install and enable the [gRPC PHP Extension][php_grpc].
Run the samples:

```sh
$ php error_reporting.php report YOUR_PROJECT_ID
Reported an error to Stackdriver
```


[0]: https://console.cloud.google.com/flows/enableapi?apiid=clouderrorreporting.googleapis.com
[1]: https://console.cloud.google.com/errors
[2]: https://console.cloud.google.com/iam-admin/serviceaccounts/
[php_grpc]: http://cloud.google.com/php/grpc