# Google Auth PHP Sample Application

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
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).
5.  Run `php auth.php`. The following commands are available and work on command line:
```
  auth-cloud-implicit                      Authenticate to a cloud client library using a service account implicitly.
  auth-cloud-explicit                      Authenticate to a cloud client library using a service account explicitly.
```
6. The following commands are available but will throw a ServiceException when
run from command-line. The Compute Engine method only works on Compute Engine,
App Engine Flexible, Cloud Functions, and Container Engine. The App Engine
method only works on App Engine Standard.
```
  auth-cloud-explicit-compute-engine       Authenticate to a cloud client library using Compute Engine credentials explicitly.
  auth-cloud-explicit-app-engine           Authenticate to a cloud client library using App Engine Standard credentials explicitly.
```
7. Run `php auth.php COMMAND --help` to print information about the usage of each command.

## Contributing changes

* See [CONTRIBUTING.md](../../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../../LICENSE)
