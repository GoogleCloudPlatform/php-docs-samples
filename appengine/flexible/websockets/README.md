# PHP websockets sample for Google App Engine Flexible Environment

This sample demonstrates how to use websockets on [Google App Engine Flexible Environment](https://cloud.google.com/appengine).

## Running locally

Use the following commands to run locally:

    ```sh
    cd php-docs-samples/appengine/flexible/cloudsql
    php -S localhost:8080
    ```

## Deploying
Refer to the [top-level README](../README.md) for instructions on running and deploying.

Note that you will have to [create a firewall rule](https://cloud.google.com/sdk/gcloud/reference/compute/firewall-rules/create) that accepts traffic on port `8000`:

	```sh
	gcloud compute firewall-rules create allow-8000 --allow=tcp:8000 --target-tags=websockets-allow-8000
	```
