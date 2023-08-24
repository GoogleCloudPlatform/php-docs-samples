# Deploy simple nginx multi-container service NGINX/PHP-FPM

A Google Cloud Project is required in order to run the sample. 

## Enable required APIs

The project should have the following API's enabled:

* Cloud Run
* Artifact Registry

```bash
gcloud services enable run.googleapis.com artifactregistry.googleapis.com
```

## Getting started

Declare the following environment variables.
```bash
# References your Google Cloud Project
export PROJECT_ID=<your-project-id>
# References your Artifact Registry repo name
export REPO_NAME="default"
# References your resource location
export REGION="us-west1"
# References final Cloud Run multi-contaienr service name
export MC_SERVICE_NAME="mc-php-example"
```

1. Authenticate in [gcloud cli](https://cloud.google.com/sdk/gcloud).

```bash
gcloud auth login
```

1. Create a repository within [Artifact Registry](https://cloud.google.com/artifact-registry).

```bash
gcloud artifacts repositories create ${REPO_NAME} --repository-format=docker
```

1. Build the `nginx` and `hellophp` container images for our multi-container service.

```bash
# Creating image from the Dockerfile within nginx/ dir.
gcloud builds submit --tag=${REGION}-docker.pkg.dev/${PROJECT_ID}/${REPO_NAME}/nginx ./nginx

# Creating image from the Dockerfile within php-app/ dir.
gcloud builds submit --tag=${REGION}-docker.pkg.dev/${PROJECT_ID}/${REPO_NAME}/php ./php-app
```

1. Deploy the multi-container service

From within `service.yaml`, customize the `service.yaml` file with your own project values by replacing
the following `PROJECT_ID`, `MC_SERVICE_NAME`, `REGION`, and `REPO_NAME`.

Once you've replaced the values, you can deploy from root directory (`hello-php-nginx-sample/`).

```sh
gcloud run services replace service.yaml
```

By default, the above command will deploy the following containers into a single service:

* `nginx`: `serving` ingress container (entrypoint)
* `hellophp`: `application` container

The Cloud Run Multi-container service will default access to port `8080`,
where `nginx` container will be listening and proxy request over to `hellophp` container at port `9000`.

Verify by using curl to send an authenticated request:

```bash
curl --header "Authorization: Bearer $(gcloud auth print-identity-token)" <cloud-run-mc-service-url>
```
