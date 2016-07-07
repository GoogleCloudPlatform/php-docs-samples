# Google Analytics and Google App Engine Flexible Environment

This sample application demonstrates how track events with Google Analytics
when running in Google App Engine Flexible Environment.

## Prerequisites

- Install [`composer`](https://getcomposer.org)
- Install dependencies by running:

```sh
$ composer install
```

## Deploy to App Engine

**Prerequisites**

- Install the [Google Cloud SDK](https://developers.google.com/cloud/sdk/).

- [Create a Google Analytics Property and obtain the Tracking ID](
    https://support.google.com/analytics/answer/1042508?ref_topic=1009620).
    Include the environment variables in app.yaml with your Tracking ID.
    For example:

    ```
    env_variables:
        GA_TRACKING_ID: your-tracking-id
    ```

    Before running the sample app locally, set the environment variables required by the app:

    ```
    export GA_TRACKING_ID=your-tracking-id
    ```

**Deploy with gcloud**

```
$ gcloud config set project YOUR_PROJECT_ID
$ gcloud app deploy
```
