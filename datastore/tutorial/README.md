# Cloud Datastore sample application

This code sample is intended to be in the following document:
https://cloud.google.com/datastore/docs/datastore-api-tutorial

The code is using
[Google Cloud Client Library for PHP](https://googlecloudplatform.github.io/google-cloud-php/#/).

To run the sample, do the following first:

1. [Enable billing](https://support.google.com/cloud/answer/6293499#enable-billing).
1. [Enable the Cloud Datastore API](https://console.cloud.google.com/flows/enableapi?apiid=datastore.googleapis.com).
1. **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).

Then use one of the following methods:

1. Run `gcloud auth application-default login`

or

1. Create a service account at the
[Service account section in the Cloud Console](https://console.cloud.google.com/iam-admin/serviceaccounts/)
1. Download the json key file of the service account.
1. Set GOOGLE_APPLICATION_CREDENTIALS environment variable pointing to that file.

Then you can run the command: `php tasks.php`
