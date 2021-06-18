# Google Auth PHP Sample Application

[![Open in Cloud Shell][shell_img]][shell_link]

[shell_img]: http://gstatic.com/cloudssh/images/open-btn.svg
[shell_link]: https://console.cloud.google.com/cloudshell/open?git_repo=https://github.com/googlecloudplatform/php-docs-samples&page=editor&working_dir=auth

## Description

This command-line application shows how to authenticate to Google Cloud APIs
using different methods. This sample uses Storage as an example, but these
methods will work on any Google Cloud API.

## Build and Run
1.  **Enable APIs** - [Enable the Storage API](https://console.cloud.google.com/flows/enableapi?apiid=storage-api.googleapis.com)
    and create a new project or select an existing project.
2.  **Download The Credentials** - Click "Go to credentials" after enabling the APIs. Click "New Credentials"
    and select "Service Account Key". Create a new service account, use the JSON key type, and
    select "Create". Once downloaded, set the environment variable `GOOGLE_APPLICATION_CREDENTIALS`
    to the path of the JSON key that was downloaded.
3.  **Clone the repo** and cd into this directory
```
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd php-docs-samples/auth
```
4.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install --no-dev` (if composer is installed locally) or `composer install --no-dev`
    (if composer is installed globally).
5.  **Run the samples** to run the auth samples, run any of the files in `src/` on the CLI:
```
$ php src/auth_api_explicit.php

Usage: auth_api_explicit.php $projectId $serviceAccountPath

    @param string $projectId           The Google project ID.
    @param string $serviceAccountPath  Path to service account credentials JSON.
```
6. The following files are available but cannot be run from the CLI. The Compute
methods only work on Compute Engine, App Engine, Cloud Functions,
and Container Engine.
```
  src/auth_cloud_explicit_compute.php
  src/auth_api_explicit_compute.php
```
7. You can test the samples that use Compute credentials by deploying to App
Engine Standard. Run `gcloud app deploy`.

## Contributing changes

* See [CONTRIBUTING.md](../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../LICENSE)
