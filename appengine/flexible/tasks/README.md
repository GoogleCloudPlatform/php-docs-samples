# Google Cloud Tasks App Engine Queue Samples

Sample command-line program for interacting with the Cloud Tasks API
using App Engine queues.

App Engine queues push tasks to an App Engine HTTP target. This directory
contains both the App Engine app to deploy, as well as the snippets to run
locally to push tasks to it, which could also be called on App Engine.

`tasks.php` is a simple command-line program to create tasks to be pushed to
the App Engine app.

`src/create_task.php` is a simple function to create tasks to be pushed to
the App Engine app.

`index.php` is the main App Engine app. This app serves as an endpoint to receive
App Engine task attempts.

`app.yaml` configures the App Engine app.

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
    $ cd php-docs-samples/appengine/flexible/tasks
    ```
4.  **Install dependencies** via [Composer](http://getcomposer.org/doc/00-intro.md).
    Run `php composer.phar install` (if composer is installed locally) or `composer install`
    (if composer is installed globally).

## Creating a queue

To create a queue using the Cloud SDK, use the following gcloud command:

    gcloud tasks queues create-app-engine-queue my-appengine-queue

Note: A newly created queue will route to the default App Engine service and
version unless configured to do otherwise.

## Deploying the App Engine app

Deploy the App Engine app with gcloud:

    gcloud app deploy

Verify the index page is serving:

    gcloud app browse

The App Engine app serves as a target for the push requests. It has an
endpoint `/example_task_handler` that reads the payload (i.e., the request
body) of the HTTP POST request and logs it to the `my-log` log. The log output can be viewed with:

    gcloud logging read my-log

## Running the Samples

Set environment variables:

First, your project ID:

    export PROJECT_ID=my-project-id

Then the queue ID, as specified at queue creation time. Queue IDs already
created can be listed with `gcloud tasks queues list`.

    export QUEUE_ID=my-appengine-queue

And finally the location ID, which can be discovered with
`gcloud tasks queues describe $QUEUE_ID`, with the location embedded in
the "name" value (for instance, if the name is
"projects/my-project/locations/us-central1/queues/my-appengine-queue", then the
location is "us-central1").

    export LOCATION_ID=us-central1

Create a task, targeted at the `example_task_handler` endpoint, with a payload specified:

    php tasks.php create-task $PROJECT_ID $QUEUE_ID $LOCATION_ID --payload=hello

Now view that the payload was received and verify the payload:

    gcloud logging read my-log

Create a task that will be scheduled for a time in the future using the
`--seconds` flag:

    php tasks.php create-task $PROJECT_ID $QUEUE_ID $LOCATION_ID --payload=hello --seconds=2
