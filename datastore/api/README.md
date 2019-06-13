# Cloud Datastore code snippets

These samples show how to use the [Datastore API][datastore]
from PHP.

[datastore]: https://cloud.google.com/datastore/docs/reference/libraries

The code is using
[Google Cloud Client Library for PHP](https://googlecloudplatform.github.io/google-cloud-php/#/).

To run the tests do the following:

1. [Enable billing](https://support.google.com/cloud/answer/6293499#enable-billing).
1. [Enable the Cloud Datastore API](https://console.cloud.google.com/flows/enableapi?apiid=datastore.googleapis.com).
1. Create a service account at the
   [Service account section in the Cloud Console](https://console.cloud.google.com/iam-admin/serviceaccounts/)
1. Download the json key file of the service account.
1. Set GOOGLE_APPLICATION_CREDENTIALS environment variable pointing to that file.
1. **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).
1. Create Datastore indexes by running `gcloud datastore indexes create index.yaml`
1. Check the [Indexes](https://console.cloud.google.com/datastore/indexes) page to verify the indexes have been created.
1. Run `phpunit`
