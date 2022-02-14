Google Cloud Compute Engine PHP Samples - Firewall
==================================================

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.svg
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=compute/cloud-client/instances

This directory contains samples for calling [Google Cloud Compute Engine][compute] APIs
from PHP. Specifically, they show how to manage your [VPC firewall rules][firewall_rules].

[compute]: https://cloud.google.com/compute/docs/apis
[firewall_rules]: https://cloud.google.com/vpc/docs/firewalls

## Setup

### Authentication

Authentication is typically done through [Application Default Credentials][adc]
which means you do not have to change the code to authenticate as long as
your environment has credentials. You have a few options for setting up
authentication:

1. When running locally, use the [Google Cloud SDK][google-cloud-sdk]

        gcloud auth application-default login

1. When running on App Engine or Compute Engine, credentials are already
   set. However, you may need to configure your Compute Engine instance
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

1. **Install dependencies** using [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).

1. Create a [service account](https://cloud.google.com/iam/docs/creating-managing-service-accounts#creating).

1. [Download the json key file](https://cloud.google.com/iam/docs/creating-managing-service-account-keys#getting_a_service_account_key)
   of the service account.

1. Set the `GOOGLE_APPLICATION_CREDENTIALS` environment variable pointing to that file.

## Samples

To run the Compute samples, run any of the files in `src/` on the CLI to print
the usage instructions:

```
$ php list_firewall_rules.php 

Usage: list_firewall_rules.php $projectId

  @param string $projectId Project ID or project number of the Cloud project you want to list rules from.
```

### Create a firewall rule

```
$ php src/create_firewall_rule.php $YOUR_PROJECT_ID "my-firewall-rule"
Created rule my-firewall-rule
```

### List firewall rules

```
$ php src/list_firewall_rules.php $YOUR_PROJECT_ID
--- Firewall Rules ---
 -  default-allow-icmp : Allow ICMP from anywhere : https://www.googleapis.com/compute/v1/projects/$YOUR_PROJECT_ID/global/networks/default
 -  default-allow-internal : Allow internal traffic on the default network : https://www.googleapis.com/compute/v1/projects/$YOUR_PROJECT_ID/global/networks/default
```

### Print firewall rule

```
$ php src/print_firewall_rule.php $YOUR_PROJECT_ID "my-firewall-rule"
ID: $ID
Kind: compute#firewall
Name: my-firewall-rule
Creation Time: $TIMESTAMP
Direction: INGRESS
Network: https://www.googleapis.com/compute/v1/projects/$YOUR_PROJECT_ID/global/networks/default
Disabled: false
Priority: 100
Self Link: https://www.googleapis.com/compute/v1/projects/$YOUR_PROJECT_ID/global/firewalls/my-firewall-rule
Logging Enabled: false
--Allowed--
Protocol: tcp
 - Ports: 80
 - Ports: 443
--Source Ranges--
 - Range: 0.0.0.0/0
```

### Delete a firewall rule

```
$ php src/delete_firewall_rule.php $YOUR_PROJECT_ID "my-firewall-rule"
Rule my-firewall-rule deleted successfully!
```

### Set firewall rule priority

```
$ php src/patch_firewall_priority.php $YOUR_PROJECT_ID "my-firewall-rule" 100
Patched my-firewall-rule priority to 100.
```

## Troubleshooting

If you get the following error, set the environment variable `GCLOUD_PROJECT` to your project ID:

```
[Google\Cloud\Core\Exception\GoogleException]
No project ID was provided, and we were unable to detect a default project ID.
```

## The client library

This sample uses the [Google Cloud Compute Client Library for PHP][google-cloud-php].
You can read the documentation for more details on API usage and use GitHub
to [browse the source][google-cloud-php-source] and [report issues][google-cloud-php-issues].

[google-cloud-php]: https://googleapis.github.io/google-cloud-php/#/docs/google-cloud/v0.152.0/compute/readme
[google-cloud-php-source]: https://github.com/GoogleCloudPlatform/google-cloud-php
[google-cloud-php-issues]: https://github.com/GoogleCloudPlatform/google-cloud-php/issues
[google-cloud-sdk]: https://cloud.google.com/sdk/
