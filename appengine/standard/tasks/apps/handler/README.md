# Google Cloud Tasks Handler on App Engine Standard for PHP 7.2

The Task Handler sample application demonstrates how to handle a task from a Cloud Tasks Appengine Queue.

## Setup the sample

- Install [`composer`](https://getcomposer.org)
- Install dependencies by running:
    ```sh
    composer install
    ```
- Install the [Google Cloud SDK](https://developers.google.com/cloud/sdk/).

## Deploy the sample

### Deploy with `gcloud`

Deploy the samples by doing the following:

```
gcloud config set project YOUR_PROJECT_ID
gcloud app deploy
gcloud app browse
```

The last command will open `https://{YOUR_PROJECT_ID}.appspot.com/`
in your browser. Browse to `/` to send in some logs.

### Run Locally

Run the sample locally using PHP's build-in web server:

```
# export environemnt variables locally which are set by App Engine when deployed
export GOOGLE_APPLICATION_CREDENTIALS=/path/to/credentials.json
export GOOGLE_CLOUD_PROJECT=YOUR_PROJECT_ID

# Run PHP's built-in web server
php -S localhost:8000
```

Browse to `localhost:8000` to send in the logs.

> Note: These logs will show up under the `Global` resource since you are not
actually sending these from App Engine.

## Contributing changes

* See [CONTRIBUTING.md](../../CONTRIBUTING.md)

## Licensing

* See [LICENSE](../../LICENSE)
