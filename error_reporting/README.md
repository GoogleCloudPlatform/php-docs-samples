# Stackdriver Error Reporting

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.png
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=error_reporting

`quickstart.php` and `error_reporting.php` are simple command-line programs to demonstrate logging
exceptions, errors, and PHP fatral errors to Stackdriver Error Reporting.

# Installation

1. To use this sample, you must first [enable the Stackdriver Error Reporting API][0]
1. Next, **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md):
    1. Run `php composer.phar install --ignore-platform-reqs` (if composer is installed locally) or `composer install --ignore-platform-reqs`
    (if composer is installed globally).
    ```sh
    composer install --ignore-platform-reqs
    ```
    1. If the [gRPC PHP Extension][php_grpc] is enabled for your version of PHP,
    install your dependencies without the `--ignore-platform-reqs` flag. **Note**
    some samples in `error_reporting.php` require gRPC.
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
Exception logged to Stack Driver Error Reporting
```

View [Stackdriver Error Reporting][1] in the Cloud Console to see the logged
exception.

# Running error_reporting.php

Run the sample:

```sh
$ php error_reporting.php report YOUR_PROJECT_ID
Reported an error to Stackdriver
```

For an example of how to register the Stackdriver exception handler in your custom application, see
[src/register_exception_handler.php](src/register_exception_handler.php). You can test this out
using the samples:

```sh
# Test registering an exception handler and then throwing a PHP Fatal Error
$ php error_reporting.php test-exception-handler YOUR_PROJECT_ID --fatal
Triggering a PHP Fatal Error by eval-ing a syntax error...
```

For more granular control over your error reporting, and better performance, you can use the gRPC
library to throw errors. Follow the instructions to install and enable the
[gRPC PHP Extension][php_grpc]. Now run the gRPC example in `error_reporting.php`:

```sh
$ php error_reporting.php report-grpc YOUR_PROJECT_ID
Reported an error to Stackdriver
```

[0]: https://console.cloud.google.com/flows/enableapi?apiid=clouderrorreporting.googleapis.com
[1]: https://console.cloud.google.com/errors
[2]: https://console.cloud.google.com/iam-admin/serviceaccounts/
[php_grpc]: http://cloud.google.com/php/grpc