<img src="https://avatars2.githubusercontent.com/u/2810941?v=3&s=96" alt="Google Cloud Platform logo" title="Google Cloud Platform" align="right" height="96" width="96"/>

# Eventarc PHP Samples

This directory contains samples for Eventarc from PHP.

## Samples

|                 Sample                  |        Description       |     Deploy    |
| --------------------------------------- | ------------------------ | ------------- |
|[Generic][generic]  | Quickstart | [<img src="https://storage.googleapis.com/cloudrun/button.svg" alt="Run on Google Cloud" height="30"/>][run_button_generic] |

## Setup

1. [Set up for Cloud Run development](https://cloud.google.com/run/docs/setup)

2. Clone this repository:

    ```sh
    git clone https://github.com/GoogleCloudPlatform/php-docs-samples.git
    cd php-docs-samples/eventarc
    ```

## How to run a sample locally

1. [Install docker locally](https://docs.docker.com/install/)

2. [Build the sample container](https://cloud.google.com/run/docs/building/containers#building_locally_and_pushing_using_docker):

    ```sh
    export SAMPLE='generic'
    cd $SAMPLE
    docker build --tag "eventarc-$SAMPLE" .
    ```

3. [Run containers locally](https://cloud.google.com/run/docs/testing/local)

    With the built container:

    ```sh
    PORT=8080 && docker run --rm -p 8080:${PORT} -e PORT=${PORT} $SAMPLE
    ```

    Test the web server with `cURL`:

    ```sh
    curl -XPOST localhost:8080 -d '{ "test": "foo" }'
    ```

    Observe the output logs your HTTP request.

    Exit the container with `Ctrl-D`.

## Deploying

See [Building containers][run_build] and [Deploying container images][run_deploy]
for more information.

[run_docs]: https://cloud.google.com/run/docs/
[run_build]: https://cloud.google.com/run/docs/building/containers
[run_deploy]: https://cloud.google.com/run/docs/deploying
[generic]: generic/
[run_button_generic]: https://deploy.cloud.run/?dir=eventarc/generic
