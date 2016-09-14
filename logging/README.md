# Stackdriver Logging v2 API Samples

`logging.php` is a simple command-line program to demonstrate writing to a log,
listing its entries, deleting it, interacting with sinks to export logs to
Google Cloud Storage.

To use logging sinks, you will also need a Google Cloud Storage Bucket.

    gsutil mb gs://[YOUR_PROJECT_ID]

You must add Cloud Logging as an owner to the bucket. To do so, add
`cloud-logs@google.com` as an owner to the bucket. See the
[exportings logs](https://cloud.google.com/logging/docs/export/configure_export#configuring_log_sinks)
docs for complete details. 

# Running locally

Use the [Cloud SDK](https://cloud.google.com/sdk) to provide authentication:

    gcloud beta auth application-default login

Run the samples:

    ```
    php logging.php list # For getting sub command list
    php logging.php help write # For showing help for write sub command `write`
    ```
