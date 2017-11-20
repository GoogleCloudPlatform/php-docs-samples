# gRPC for App Engine (standard)

This app demonstrates how to run gRPC client libraries on App Engine Standard.

## Setup

- Install [`composer`](https://getcomposer.org)
- Install dependencies by running:

    ```sh
    composer install
    ```

- Install the [Google Cloud SDK](https://developers.google.com/cloud/sdk/).

## Deploy to App Engine

### Run Locally

These samples cannot be run locally with the Dev AppServer because gRPC has not
been packaged with the Dev AppServer for PHP at this time.

### Deploy with gcloud

**The Cloud Monitoring sample**

The monitoring sample will work out of the box by doing the following:


```
gcloud config set project YOUR_PROJECT_ID
gcloud app deploy
gcloud app browse
```

The last command will open `https://{YOUR_PROJECT_ID}.appspot.com/`
in your browser. Browse to `/monitoring` to see the sample.

**The Cloud Spanner sample**

You will need to create a [Spanner Instance][create_instance] and a
[Spanner Database][create_database].

Next, open up `spanner.php` in a text editor and change the values of
`your-instance-id` and `your-database-id` to the Instance ID and Database ID you
created.

Now you can deploy your application and it will work as expected:

```
gcloud config set project YOUR_PROJECT_ID
gcloud app deploy
gcloud app browse
```

The last command will open `https://{YOUR_PROJECT_ID}.appspot.com/`
in your browser. Browse to `/spanner` to see the sample.

[create_database]: https://cloud.google.com/spanner/docs/quickstart-console#create_a_database
[create_instance]: https://cloud.google.com/spanner/docs/quickstart-console#create_an_instance
