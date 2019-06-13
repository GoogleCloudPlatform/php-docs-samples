# Stackdriver Error Reporting

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.svg
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=error_reporting


This directory contains samples for setting up and using
[Stackdriver Error Reporting][error-reporting] for PHP.

[error-reporting]: https://cloud.google.com/error-reporting/docs/setup/php

`quickstart.php` and `src/report_error.php` are simple command-line programs to
demonstrate logging exceptions, errors, and PHP fatral errors to
Stackdriver Error Reporting.

# Installation

1. To use this sample, you must first [enable the Stackdriver Error Reporting API][0]
1. Next, **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md):
    1. Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).
    ```sh
    composer install
    ```
    1. To use the [gRPC PHP Extension][php_grpc], which will be more performant than
    REST/HTTP,
    install and enable the gRPC extension using PECL:
    ```sh
    pecl install grpc
    ```
1. Create a service account in the [Service Account section of the Cloud Console][2]
1. Download the JSON key file of the service account.
1. Set `GOOGLE_APPLICATION_CREDENTIALS` environment variable to point to that file.
	```sh
	export GOOGLE_APPLICATION_CREDENTIALS=/path/to/service_account_credentials.json
	```

# Running quickstart.php

Open `quickstart.php` in a text editor and replace the text `YOUR_PROJECT_ID`
with your Project ID.

Run the samples:

```sh
php quickstart.php
Throwing a test exception. You can view the message at https://console.cloud.google.com/errors.
```

This example registers the Stackdriver exception handler using
[PHP exception handlers][3]. View [Stackdriver Error Reporting][1] in the Cloud
Console to see the logged exception.

# Running src/report_error.php

This sample shows how to report an error by creating a `ReportedErrorEvent`. The
`ReportedErrorEvent` object gives you more control over how the error appears
and the details associated with it.

Run the sample:

```sh
$ php src/report_error.php YOUR_PROJECT_ID "This is a test message"
Reported an exception to Stackdriver
```

View [Stackdriver Error Reporting][1] in the Cloud Console to see the logged
exception.

[0]: https://console.cloud.google.com/flows/enableapi?apiid=clouderrorreporting.googleapis.com
[1]: https://console.cloud.google.com/errors
[2]: https://console.cloud.google.com/iam-admin/serviceaccounts/
[3]: http://php.net/manual/en/function.set-exception-handler.php
[php_grpc]: http://cloud.google.com/php/grpc
