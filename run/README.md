<img src="https://avatars2.githubusercontent.com/u/2810941?v=3&s=96" alt="Google Cloud Platform logo" title="Google Cloud Platform" align="right" height="96" width="96"/>

# Google Cloud Run PHP Samples

[Cloud Run][run_docs] runs stateless [containers](https://cloud.google.com/containers/) on a fully managed environment or in your own GKE cluster.

## Samples

|                 Sample                  |        Description       |     Deploy    |
| --------------------------------------- | ------------------------ | ------------- |
|[Hello World][helloworld]  | Quickstart | [<img src="https://storage.googleapis.com/cloudrun/button.svg" alt="Run on Google Cloud" height="30"/>][run_button_helloworld] |

For more Cloud Run samples beyond PHP, see the main list in the [Cloud Run Samples repository](https://github.com/GoogleCloudPlatform/cloud-run-samples).

## Setup

1. [Set up for Cloud Run development](https://cloud.google.com/run/docs/setup)

2. Clone this repository:

    ```sh
    git clone https://github.com/GoogleCloudPlatform/php-docs-samples.git
    cd php-docs-samples/run
    ```

## How to run a sample locally

1. [Install docker locally](https://docs.docker.com/install/)

2. [Build the sample container](https://cloud.google.com/run/docs/building/containers#building_locally_and_pushing_using_docker):

    ```sh
    export SAMPLE='helloworld'
    cd $SAMPLE
    docker build --tag $SAMPLE .
    ```

3. [Run containers locally](https://cloud.google.com/run/docs/testing/local)

    With the built container:

    ```sh
    PORT=8080 && docker run --rm -p 8080:${PORT} -e PORT=${PORT} $SAMPLE
    ```

    Overriding the built container with local code:

    ```sh
    PORT=8080 && docker run --rm \
        -p 8080:${PORT} -e PORT=${PORT} \
        -v $PWD:/usr/src/app $SAMPLE
    ```

    Injecting your service account key:

    ```sh
    export SA_KEY_NAME=my-key-name-123
    PORT=8080 && docker run --rm \
        -p 8080:${PORT} -e PORT=${PORT} \
        -e GOOGLE_APPLICATION_CREDENTIALS=/tmp/keys/${SA_KEY_NAME}.json \
        -v $GOOGLE_APPLICATION_CREDENTIALS:/tmp/keys/${SA_KEY_NAME}.json:ro \
        -v $PWD:/usr/src/app $SAMPLE
    ```

    Opening a shell in the container:

    1. Build the container.

    2. Run the container with a shell:

       ```sh
       PORT=8080 && docker run --rm \
           --interactive --tty \
           -p 8080:${PORT} -e PORT=${PORT} \
           -v $PWD:/var/www/html $SAMPLE \
           /bin/bash
       ```

    3. Exit the container: `Ctrl-D`

## Deploying

See [Building containers][run_build] and [Deploying container images][run_deploy]
for more information.

[run_docs]: https://cloud.google.com/run/docs/
[run_build]: https://cloud.google.com/run/docs/building/containers
[run_deploy]: https://cloud.google.com/run/docs/deploying
[helloworld]: helloworld/
[run_button_helloworld]: https://deploy.cloud.run/?dir=run/helloworld
