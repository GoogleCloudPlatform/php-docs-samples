# Google Analytics Data API Samples

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.svg
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=analyticsdata

## Description

These samples show how to use the [Google Analytics Data API][analyticsdata-api]
from PHP.

[analyticsdata-api]: https://developers.google.com/analytics/devguides/reporting/data/v1

## Build and Run
1.  **Enable APIs** - [Enable the Analytics Data API](https://console.cloud.google.com/flows/enableapi?apiid=analyticsdata.googleapis.com)
    and create a new project or select an existing project.
2.  **Download The Credentials** - Configure your project using [Application Default Credentials][adc].
    Click "Go to credentials" after enabling the APIs. Click "Create Credentials"
    and select "Service Account Credentials" and download the credentials file. Then set the path to
    this file to the environment variable `GOOGLE_APPLICATION_CREDENTIALS`:
```sh
    $ export GOOGLE_APPLICATION_CREDENTIALS=/path/to/credentials.json
```
3.  **Clone the repo** and cd into this directory
```sh
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd php-docs-samples/analyticsdata
```
4.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).
5.  **Replace `$property_id` variable** if present in the snippet with the
value of the Google Analytics 4 property id you want to access.
6.  **Run** with the command `php SNIPPET_NAME.php`. For example:
```sh
    $ php quickstart.php
```

## Contributing changes

* See [CONTRIBUTING.md](../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../LICENSE)

[adc]: https://cloud.google.com/docs/authentication/production#obtaining_and_providing_service_account_credentials_manually
