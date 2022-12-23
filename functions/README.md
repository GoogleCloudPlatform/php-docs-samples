<img src="https://avatars2.githubusercontent.com/u/2810941?v=3&s=96" alt="Google Cloud Platform logo" title="Google Cloud Platform" align="right" height="96" width="96"/>

# Google Cloud Functions samples

This directory contains samples for Google Cloud Functions. Each sample can be run locally by calling the following:

```
cd SAMPLE_DIR
composer install
composer start
```

Each sample can be deloyed to Google Cloud Functions by calling the following:

```sh
cd SAMPLE_DIR
gcloud functions deploy FUNCTION_NAME --runtime php81 --trigger-http --allow-unauthenticated
```

For more information, see
[Create and deploy a Cloud Function by using the Google Cloud CLI](https://cloud.google.com/functions/docs/create-deploy-gcloud), or see the
[list of all Cloud Functions samples](https://cloud.google.com/functions/docs/samples).
