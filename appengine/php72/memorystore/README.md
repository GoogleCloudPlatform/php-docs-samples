# Connect to Cloud Memorystore from Google App Engine

This sample application demonstrates how to use
[Cloud Memorystore for Redis](https://cloud.google.com/memorystore/docs/)
with Google App Engine for PHP 7.2.

**Prerequisites**

- Install the [Google Cloud SDK](https://developers.google.com/cloud/sdk/).

## Setup

Before you can run or deploy the sample, you will need to do the following:

1. Create a [Memorystore instance][memorystore_create]. You can do this from the
   [Cloud Console](https://console.developers.google.com) or via the
   [Cloud SDK](https://cloud.google.com/sdk). To create it via the SDK use the
   following command:

        $ gcloud beta redis instances create YOUR_INSTANCE_NAME --region=REGION_ID

1. Update the environment variables `REDIS_HOST` and `REDIS_PORT` in `app.yaml`
   with your configuration values. These values are used when the application is
   deployed. Run the following command to get the values for your isntance:

        $ gcloud beta redis instances describe YOUR_INSTANCE_NAME --region=REGION_ID

[memorystore_create]: https://cloud.google.com/memorystore/docs/redis/creating-managing-instances

## Run locally

You can connect to a local database instance by setting the `REDIS_` environment
variables to your local instance. Alternatively, you can set them to your Cloud
Memorystore instance, but you will need to create a firewall rule for this,
which may be a safety concern.

```sh
cd php-docs-samples/appengine/php72/memorystore

# set local connection parameters
export REDIS_HOST=127.0.0.1
export REDIS_PORT=6379

php -S localhost:8080
```

> be sure the `REDIS_` environment variables are appropriate for your Redis
  instance.

Now you can view the app running at [http://localhost:8080](http://localhost:8080)
in your browser.

## Set up Serverless VPC Access

**IMPORTANT** App Engine requires a [Serverless VPC Access][vpc-access]
connector to connect to Memorystore.

In order for App Engine to connect to Memorystore, you must first
[configure a Serverless VPC Access connector][configure-vpc]. For example:

```
# Example command to create a VPC connector. See the docs for more details.
$ gcloud beta compute networks vpc-access connectors create CONNECTOR_NAME \
	--region=REGION_ID \
	--range="10.8.0.0/28"
	--project=PROJECT_ID
```

Next, you neded to [configure App Engine to connect to your VPC network][connecting-appengine].
This is done by modifying [`app.yaml`](app.yaml) and setting the full name of
the connector you just created under `vpc_access_connector`.

```
vpc_access_connector:
  name: "projects/PROJECT_ID/locations/REGION_ID/connectors/CONNECTOR_NAME"
```

**Note**: Serverless VPC Access incurs additional billing. See
[pricing][vpc-pricing] for details.

[vpc-access]: https://cloud.google.com/vpc
[configure-vpc]: https://cloud.google.com/vpc/docs/configure-serverless-vpc-access
[connecting-appengine]: https://cloud.google.com/appengine/docs/standard/python/connecting-vpc#configuring
[vpc-pricing]: https://cloud.google.com/compute/pricing#network

## Deploy to App Engine

**IMPORTANT** Because Serverless VPC Connector is in *beta*, you must deploy to App Engine
using the `gcloud beta app deploy` command. Without this, the connection to
Memorystore *will not work*.

```
$ gcloud beta app deploy --project PROJECT_ID
```

Now open `https://{YOUR_PROJECT_ID}.appspot.com/` in your browser to see the running
app.

**Note**: This example requires the `redis.so` extension to be enabled on your App Engine
instance. This is done by including [`php.ini`](php.ini) in your project root.

## Troubleshooting

### Connection timed out

If you receive the error "Error: Connection timed out", it's possible your VPC Connector
is not fully set up. Run the following and check the property `state` is set to READY:

```
$ gcloud beta compute networks vpc-access connectors describe CONNECTOR_NAME --region=REGION_ID
```

If you continue to see the timeout error, try creating a new VPC connector with a different
CIDR `range`.

### Name or service not known

If you receive the following error, make sure you set the `REDIS_HOST` environment variable in `app.yaml` to be the
host of your Memorystore for Redis instance.

```
PHP message: PHP Warning: Redis::connect(): php_network_getaddresses: getaddrinfo failed: Name or service not known in /srv/index.php
```

### Request contains an invalid argument

If you receive this error, it is because either the `gcloud` command to create the VPC 
Access connector was missing the `--range` option, or the value supplied to the
`--range` option did not use the `/28` CIDR mask as required. Be sure to supply a valid
CIDR range ending in `/28`.
