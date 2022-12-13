Stackdriver Monitoring PHP Samples
==================================

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.svg
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=monitoring

This directory contains samples for calling [Stackdriver Monitoring][monitoring]
from PHP.

Stackdriver Monitoring collects metrics, events, and metadata from
Google Cloud Platform, Amazon Web Services (AWS), hosted uptime probes,
application instrumentation, and a variety of common application components
including Cassandra, Nginx, Apache Web Server, Elasticsearch and many others.

[monitoring]: https://cloud.google.com/monitoring/docs/reference/libraries

## Setup

### Authentication

Authentication is typically done through [Application Default Credentials][adc]
which means you do not have to change the code to authenticate as long as
your environment has credentials. You have a few options for setting up
authentication:

1. When running locally, use the [Google Cloud SDK][google-cloud-sdk]

        gcloud auth application-default login

1. When running on App Engine or Compute Engine, credentials are already
   set-up. However, you may need to configure your Compute Engine instance
   with [additional scopes][additional_scopes].

1. You can create a [Service Account key file][service_account_key_file]. This file can be used to
   authenticate to Google Cloud Platform services from any environment. To use
   the file, set the ``GOOGLE_APPLICATION_CREDENTIALS`` environment variable to
   the path to the key file, for example:

        export GOOGLE_APPLICATION_CREDENTIALS=/path/to/service_account.json

[adc]: https://cloud.google.com/docs/authentication#getting_credentials_for_server-centric_flow
[additional_scopes]: https://cloud.google.com/compute/docs/authentication#using
[service_account_key_file]: https://developers.google.com/identity/protocols/OAuth2ServiceAccount#creatinganaccount

## Install Dependencies

1. Ensure the [gRPC PHP Extension][php_grpc] is installed and enabled on your machine.
1. [Enable the Stackdriver Monitoring API](https://console.cloud.google.com/flows/enableapi?apiid=monitoring.googleapis.com).

1. **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).

1. Create a service account at the
[Service account section in the Cloud Console](https://console.cloud.google.com/iam-admin/serviceaccounts/)

1. Download the json key file of the service account.

1. Set `GOOGLE_APPLICATION_CREDENTIALS` environment variable pointing to that file.

## Stackdriver Monitoring Samples

Execute the snippets in the [src/](src/) directory by running
`php src/SNIPPET_NAME.php`. The usage will print for each if no arguments
are provided:
```sh
$ php src/list_resources.php
Usage: php src/list_resources.php PROJECT_ID

$ php src/list_resources.php 'your-project-id'
```

## The client library

This sample uses the [Cloud Monitoring Client Library for PHP][google-cloud-php-monitoring].
You can read the documentation for more details on API usage and use GitHub
to [browse the source][google-cloud-php-source] and  [report issues][google-cloud-php-issues].

[php_grpc]: http://cloud.google.com/php/grpc
[google-cloud-php-monitoring]: https://cloud.google.com/php/docs/reference/cloud-monitoring/latest
[google-cloud-php-source]: https://github.com/GoogleCloudPlatform/google-cloud-php
[google-cloud-php-issues]: https://github.com/GoogleCloudPlatform/google-cloud-php/issues
[google-cloud-sdk]: https://cloud.google.com/sdk/
