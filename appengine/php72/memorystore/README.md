# Cloud SQL & Google App Engine

This sample application demonstrates how to use [Cloud SQL with Google App Engine](https://cloud.google.com/appengine/docs/php/cloud-sql/).

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

## Deploy to App Engine

**IMPORTANT** App Engine requires a Serverless VPC Access connector to connect
to Memorystore.

In order for App Engine to connect to Memorystore, you must first
create a [Serverless VPC Access connector][vpc-access].

Next, you neded to [configure App Engine to connect to your VPC network]

[vpc-access]: https://cloud.google.com/vpc/docs/configure-serverless-vpc-access
[connecting-appengine]: https://cloud.google.com/appengine/docs/standard/python/connecting-vpc#configuring

**Prerequisites**

- Install the [Google Cloud SDK](https://developers.google.com/cloud/sdk/).

**Deploy with gcloud**

```
gcloud config set project YOUR_PROJECT_ID
gcloud app deploy
gcloud app browse
```

The last command will open `https://{YOUR_PROJECT_ID}.appspot.com/`
in your browser.
