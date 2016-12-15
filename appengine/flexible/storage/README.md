# Cloud Storage & Google App Engine

This sample application demonstrates how to use [Cloud Storage with Google App Engine](https://cloud.google.com/appengine/docs/flexible/php/using-cloud-storage).

## Setup

Before running this sample:

## Prerequisites

- Install [`composer`](https://getcomposer.org)
- Install dependencies by running:

    ```sh
    composer install
    ```

- Install the [Google Cloud SDK](https://developers.google.com/cloud/sdk/).
- [Initialize the SDK](https://cloud.google.com/sdk/docs/quickstart-mac-os-x#initialize_the_sdk)

## Run Locally

Set the environment variables `GOOGLE_BUCKET_NAME` and ``GCLOUD_PROJECT` to the name of your storage bucket and project ID respectively.

```
export GOOGLE_BUCKET_NAME=your-bucket-name
export GCLOUD_PROJECT=your-project-id
```

Run the sample with the PHP built-in web server:

```
php -S localhost:8080
```

> Note: Your PHP executable path may be different than the one above.

Now browse to `http://localhost:8080` to view the sample.

## Deploy to App Engine

**Prerequisites**

- Set `your-bucket-name` in `app.yaml` to the name of your Cloud Storage Bucket.

**Deploy with gcloud**

```
gcloud config set project YOUR_PROJECT_ID
gcloud app deploy
gcloud app browse
```

The last command will open `https://{YOUR_PROJECT_ID}.appspot.com/`
in your browser.
