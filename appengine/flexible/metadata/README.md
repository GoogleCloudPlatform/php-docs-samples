# Compute Metadata on App Engine Flexible Environment

This sample application demonstrates how to access
[Compute Metadata](https://cloud.google.com/compute/docs/storing-retrieving-metadata)
from App Engine flexible environment.

## Setup

Before running this sample:

### Register your application

- Go to
  [Google Developers Console](https://console.developers.google.com/project)
  and create a new project. 

### Prerequisites

- Install [`composer`](https://getcomposer.org)
- Install dependencies by running:

    ```sh
    composer install
    ```

- Install the [Google Cloud SDK](https://developers.google.com/cloud/sdk/).
- Initialize the SDK by running `gcloud init`

## Run Locally

This sample is designed to run in App Engine flexible environment.
This application will fail to reach the Metadata server if run locally.

## Deploy to App Engine

**Deploy with gcloud**

```
gcloud config set project YOUR_PROJECT_ID
gcloud app deploy
gcloud app browse
```

The last command will open `https://{YOUR_PROJECT_ID}.appspot.com/`
in your browser.
