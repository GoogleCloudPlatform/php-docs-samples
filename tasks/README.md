# Google Cloud Tasks Samples

## Description

Al code in the snippets directory demonstrate how to invoke
[Cloud Tasks][cloud-tasks] from PHP.

`src/create_http_task.php` is a simple function to create tasks with an HTTP target.

[cloud-tasks]: https://cloud.google.com/tasks/docs/quickstart-appengine

## Setup:

1.  **Enable APIs** - [Enable the Cloud Tasks API](https://console.cloud.google.com/flows/enableapi?apiid=cloudtasks)
    and create a new project or select an existing project.
2.  **Download The Credentials** - Click "Go to credentials" after enabling the APIs. Click "New Credentials"
    and select "Service Account Key". Create a new service account, use the JSON key type, and
    select "Create". Once downloaded, set the environment variable `GOOGLE_APPLICATION_CREDENTIALS`
    to the path of the JSON key that was downloaded.
3.  **Clone the repo** and cd into this directory

    ```sh
    $ git clone https://github.com/GoogleCloudPlatform/php-docs-samples
    $ cd php-docs-samples/tasks
    ```
4.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).
5.  Create a Queue
    To create a queue using the Cloud SDK, use the following gcloud command:
    ```sh
    gcloud tasks queues create <QUEUE_NAME>
    ```

## Using an HTTP Target
1. Run `php src/create_http_task.php`. The usage will print for each if no arguments are provided:

    ```
    $> php src/create_http_task.php
    Usage: php src/create_http_task.php PROJECT_ID LOCATION_ID QUEUE_ID URL [PAYLOAD]
    ```

    where:
    * `PROJECT_ID` is your Google Cloud Project id.
    * `QUEUE_ID` is your queue id.
      Queue IDs already created can be listed with `gcloud tasks queues list`.
    * `LOCATION_ID` is the location of your queue.  
      Determine the location ID, which can be discovered with
      `gcloud tasks queues describe <QUEUE_NAME>`, with the location embedded in
      the "name" value (for instance, if the name is
      "projects/my-project/locations/us-central1/queues/my-queue", then the
      location is "us-central1").
    * `URL` is the full URL to your target endpoint.

## Contributing changes

* See [CONTRIBUTING.md](../../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../../LICENSE)
