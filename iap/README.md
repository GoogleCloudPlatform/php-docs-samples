# Google Cloud Identity Aware Proxy Samples

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.svg
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=iap

These samples show how to use the [Google Cloud Identity Aware Proxy][iap]. Cloud Identity-Aware Proxy (Cloud IAP) controls access to your cloud applications running on Google Cloud Platform. Cloud IAP works by verifying a user’s identity and determining if that user should be allowed to access the application.

If this is your first time using the Google Cloud Identity Aware Proxy, try out our [quickstart tutorial][iap-quickstart].

Visit the [Programmatic authentication][iap-programmatic-authentication] and [Securing your app with signed headers][iap-signed-headers] tutorials to learn more about how these code samples work.

You can also learn more by reading the [Cloud IAP conceptual overview][iap-conceptual-overview].

## Setup

1. Deploy this [basic web application to App Engine][iap-app-engine].
1. Once the application is deployed, enable Cloud IAP for it using the Enabling Cloud IAP section of [this tutorial][iap-enable].
1. [Create a service account][create-service-account] that you will later use to access your Cloud IAP protected site. Give it the role of 'Project > Owner' and check the box for 'Furnish a new private key'.
1. Save the service account key you created in the previous step to your local computer.
1. [Grant your service account access][iap-manage-access] to your Cloud IAP application.
1. Visit the [Cloud IAP admin page][iap-console] and click the ellipses button on the same row as 'App Engine app'. Click 'Edit OAuth Client' and note the Client ID.
1. **Install dependencies** via [Composer][composer]. Run `php composer.phar install` (if composer is installed locally) or `composer install` (if composer is installed globally).

## Samples

To run the Cloud Identity Aware Proxy Samples:

    $ php iap.php
    Cloud Identity Aware Proxy

    Usage:
      command [options] [arguments]

    Options:
      -h, --help            Display this help message
      -q, --quiet           Do not output any message
      -V, --version         Display this application version
          --ansi            Force ANSI output
          --no-ansi         Disable ANSI output
      -n, --no-interaction  Do not ask any interactive question
      -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

    Available commands:
      request    Make a request to an IAP-protected resource using a service account.
      validate   Make a request to an IAP-protected resource using a service account and then validate the JWT.

### Run Request

To run the Request sample:

    $ php iap.php request [YOUR_CLOUD_IAP_URL] [YOUR_CLIENT_ID] [PATH_TO_YOUR_SERVICE_ACCOUNT]

### Run Validate

To run the Analyze Sentiment sample:

    $ php iap.php validate [YOUR_CLOUD_IAP_URL] [YOUR_CLIENT_ID] [PATH_TO_YOUR_SERVICE_ACCOUNT] [YOUR_PROJECT_NUMBER] [YOUR_PROJECT_ID]

[iap]: http://cloud.google.com/iap
[iap-quickstart]: https://cloud.google.com/iap/docs/app-engine-quickstart
[iap-app-engine]: https://github.com/GoogleCloudPlatform/python-docs-samples/tree/master/iap/app_engine_app
[iap-enable]: https://cloud.google.com/iap/docs/app-engine-quickstart#enabling_iap
[create-service-account]: https://console.cloud.google.com/iam-admin/serviceaccounts?_ga=2.249998854.-1228762175.1480648951
[iap-manage-access]: https://cloud.google.com/iap/docs/managing-access
[iap-console]: https://console.cloud.google.com/iam-admin/iap
[composer]: http://getcomposer.org/doc/00-intro.md
[iap-programmatic-authentication]: https://cloud.google.com/iap/docs/authentication-howto#authenticating_from_a_service_account
[iap-signed-headers]: https://cloud.google.com/iap/docs/signed-headers-howto
[iap-conceptual-overview]: https://cloud.google.com/iap/docs/concepts-overview