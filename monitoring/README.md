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

To run the Stackdriver Monitoring Samples:

    $ php monitoring.php

    Stackdriver Monitoring

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
      create-metric           Creates a logging metric.
      create-uptime-check     Creates an uptime check.
      delete-metric           Deletes a logging metric.
      delete-uptime-check     Deletes an uptime check config.
      get-descriptor          Gets a logging descriptor.
      help                    Displays help for a command
      list                    Lists commands
      list-descriptors        Lists logging descriptors.
      list-uptime-check-ips   Lists Uptime Check IPs.
      list-uptime-checks      Lists Uptime Check Configs.
      read-timeseries-align   Aggregates metrics for each timeseries.
      read-timeseries-fields  Reads Timeseries fields.
      read-timeseries-reduce  Aggregates metrics across multiple timeseries.
      read-timeseries-simple  Reads a timeseries.
      write-timeseries        Writes a timeseries.

## Stackdriver Monitoring Alert Samples

To run the Stackdriver Monitoring Alert Samples:

    $ php alerts.php

    Stackdriver Monitoring Alerts

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
      backup-policies   Back up alert policies.
      create-channel    Create a notification channel.
      create-policy     Create an alert policy.
      delete-channel    Delete a notification channel.
      enable-policies   Enable or disable alert policies in a project.
      help              Displays help for a command
      list              Lists commands
      list-channels     List alert channels.
      list-policies     List alert policies.
      replace-channels  Replace alert channels.
      restore-policies  Restore alert policies from a backup.

## The client library

This sample uses the [Google Cloud Client Library for PHP][google-cloud-php].
You can read the documentation for more details on API usage and use GitHub
to [browse the source][google-cloud-php-source] and  [report issues][google-cloud-php-issues].

[php_grpc]: http://cloud.google.com/php/grpc
[google-cloud-php]: https://googlecloudplatform.github.io/google-cloud-php
[google-cloud-php-source]: https://github.com/GoogleCloudPlatform/google-cloud-php
[google-cloud-php-issues]: https://github.com/GoogleCloudPlatform/google-cloud-php/issues
[google-cloud-sdk]: https://cloud.google.com/sdk/
