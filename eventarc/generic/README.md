<img src="https://avatars2.githubusercontent.com/u/2810941?v=3&s=96" alt="Google Cloud Platform logo" title="Google Cloud Platform" align="right" height="96" width="96"/>

# Eventarc – Generic – PHP Sample

This directory contains a sample for receiving a generic event using Cloud Run
and Eventarc with PHP. For testing purposes, we use Cloud Pub/Sub as an event
source for our sample.

## Setup

1. [Set up for Cloud Run development](https://cloud.google.com/run/docs/setup)

1. Install the gcloud command-line tool beta components:

    ```sh
    gcloud components install beta
    ```

1. Set the following gcloud configurations, where `PROJECT_ID` is your Google
   Cloud project ID:

    ```sh
    gcloud config set project PROJECT_ID
    gcloud config set run/region us-central1
    gcloud config set run/platform managed
    gcloud config set eventarc/location us-central1
    ```

1. [Enable the Cloud Run, Cloud Logging, Cloud Build, Pub/Sub, and Eventarc APIs][enable_apis_url].

1. Clone this repository and navigate to this directory:

    ```sh
    git clone https://github.com/GoogleCloudPlatform/php-docs-samples.git
    cd php-docs-samples/eventarc/generic
    ```

## Run the sample locally

1. [Install docker locally](https://docs.docker.com/install/)

1. [Build the container locally](https://cloud.google.com/run/docs/building/containers#building_locally_and_pushing_using_docker):

    ```sh
    docker build --tag eventarc-generic .
    ```

1. [Run containers locally](https://cloud.google.com/run/docs/testing/local)

    With the built container:

    ```sh
    PORT=8080 && docker run --rm -p 8080:${PORT} -e PORT=${PORT} eventarc-generic
    ```

    Test the web server with `cURL`:

    ```sh
    curl -XPOST localhost:8080 -d '{ "test": "foo" }'
    ```

    Observe the output logs your HTTP request:

    ```
    Event received!

    HEADERS:
    Host: localhost:8080
    User-Agent: curl/7.64.1
    Accept: */*
    Content-Length: 17
    Content-Type: application/x-www-form-urlencoded

    BODY:
    { "test": "foo" }
    ```

    Exit the container with `Ctrl-D`.

## Run the sample on Cloud Run

1. [Build the container using Cloud Build](https://cloud.google.com/run/docs/building/containers#builder)

    ```sh
    gcloud builds submit --tag gcr.io/$(gcloud config get-value project)/eventarc-generic-php
    ```

1. [Deploy the container](https://cloud.google.com/run/docs/deploying#service)

    ```sh
    gcloud run deploy eventarc-generic-php \
      --image gcr.io/$(gcloud config get-value project)/eventarc-generic-php \
      --allow-unauthenticated
    ```

    The command line will display the service URL when deployment is complete.

### Create an Eventarc Trigger

1. Create an Eventarc trigger for your Cloud Run service

    ```sh
    gcloud beta eventarc triggers create eventarc-generic-php-trigger \
      --destination-run-service=eventarc-generic-php \
      --destination-run-region=us-central1 \
      --matching-criteria="type=google.cloud.pubsub.topic.v1.messagePublished"
    ```

1. Confirm the trigger was successfully created, run:

    ```sh
    gcloud beta eventarc triggers describe eventarc-generic-php-trigger
    ```

    > Note: It can take up to 10 minutes for triggers to be fully functional.

### Send an Event

1. Find and set the Pub/Sub topic as an environment variable:

    ```sh
    export RUN_TOPIC=$(gcloud beta eventarc triggers describe eventarc-generic-php-trigger \
    --format='value(transport.pubsub.topic)')
    ```

1. Send a message to the Pub/Sub topic to generate an event:

    ```sh
    gcloud pubsub topics publish $RUN_TOPIC --message="Hello, PHP"
    ```

    The event is sent to the Cloud Run (fully managed) service, which logs the generic HTTP request.

### View an Event in Logs

1. To view the event, go to the Cloud Run (fully managed) service logs:

    1. Go to the [Google Cloud Console](https://console.cloud.google.com/run).

    1. Click the `eventarc-generic-php` service.

    1. Select the **Logs** tab.

        > Logs might take a few moments to appear. If you don't see them immediately, check again after a few moments.

    1. Look for the log message "Event received!" followed by other log entries. This log entry indicates a request was sent by Eventarc to your Cloud Run service.

### Cleaning Up

To clean up, delete the resources created above:

1. Delete the Cloud Build container:

    ```sh
    gcloud container images delete gcr.io/$(gcloud config get-value project)/eventarc-generic-php
    ```

1. Delete the Cloud Run service:

    ```sh
    gcloud run services delete eventarc-generic-php
    ```

1. Delete the Eventarc trigger:

    ```sh
    gcloud beta eventarc triggers delete eventarc-generic-php-trigger
    ```

1. Delete the Pub/Sub topic:

    ```sh
    gcloud pubsub topics delete $RUN_TOPIC
    ```

[enable_apis_url]: https://console.cloud.google.com/flows/enableapi?apiid=run.googleapis.com,logging.googleapis.com,cloudbuild.googleapis.com,pubsub.googleapis.com,eventarc.googleapis.com
[run_button_generic]: https://deploy.cloud.run/?dir=eventarc/generic
