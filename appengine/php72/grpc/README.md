# gRPC for App Engine (php72)

This app demonstrates how to run gRPC client libraries on App Engine.

## Setup

- Install [`composer`](https://getcomposer.org)
- Install dependencies by running:

    ```sh
    composer install
    ```

- Install the [Google Cloud SDK](https://developers.google.com/cloud/sdk/).
- For the Spanner sample to run, you will need to create a [Spanner Instance][create_instance] and a [Spanner Database][create_database].

## Configure

For the Spanner sample, open `spanner.php` in a text editor and change the values of
`YOUR_INSTANCE_ID` and `YOUR_DATABASE_ID` to the Instance ID and Database ID you
created above.

## Deploy

### Run Locally

These samples cannot be run locally with the Dev AppServer because gRPC has not
been packaged with the Dev AppServer for PHP at this time. You can install gRPC
locally and run them using PHP's build-in web server:

```
# export environemnt variables locally which are set by app engine when deployed
export GOOGLE_CLOUD_PROJECT=YOUR_PROJECT_ID
export GAE_INSTANCE=local

# Run PHP's built-in web server
php -S localhost:8000
```

### Deploy with gcloud

Deploy the samples by doing the following:

```
gcloud config set project YOUR_PROJECT_ID
gcloud app deploy
gcloud app browse
```

The last command will open `https://{YOUR_PROJECT_ID}.appspot.com/`
in your browser. Browse to `/monitoring.php` to see the Monitoring sample,
and `/spanner.php` to see the Spanner sample.

[create_database]: https://cloud.google.com/spanner/docs/quickstart-console#create_a_database
[create_instance]: https://cloud.google.com/spanner/docs/quickstart-console#create_an_instance
