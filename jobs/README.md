# Google Cloud Job Discovery API Samples

## Description

These samples show how to use the [Google Cloud Job Discovery API][job-discovery]
from PHP.

[job-discovery]: https://cloud.google.com/talent-solution/job-search/v2/docs/libraries

## Build and Run
1.  **Enable APIs** - [Enable the Job Discovery API](https://console.cloud.google.com/flows/enableapi?apiid=jobs.googleapis.com)
    and create a new project or select an existing project.
2.  **Activate your Credentials** - If you do not already have an active set of credentials, create and download a [JSON Service Account key](https://console.cloud.google.com/apis/credentials/serviceaccountkey). Set the environment variable `GOOGLE_APPLICATION_CREDENTIALS` as the path to the downloaded JSON file.
3.  **Clone the repo** and cd into this directory

    ```
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd php-docs-samples/jobs
    ```
4.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).
5.  Run `php quickstart.php`.

## Contributing changes

* See [CONTRIBUTING.md](../../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../../LICENSE)
