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

Declare the following environment variables, and execute the following `make` commands
to spin this up using [make](https://www.gnu.org/software/make/manual/make.html) against your own Google Cloud Project. 

```bash
# References your Google Cloud Project
export PROJECT_ID=<your-project-id>

# References your Artifact Registry repo name
export REPO_NAME="default"

# References your resource location
export REGION="us-central1"

# References final Cloud Run multi-contaienr service name
export MC_SERVICE_NAME="mc-php-example"
```

```bash
make login # authenticate & create Artifact Registry repo

make build # build nginx & php images to Artifact Registry

make deploy # deploys multi-container service with nginx/php containers
```

Once deployed, the best place to start understanding how `nginx` is using [FastCGI](https://www.nginx.com/resources/wiki/start/topics/examples/fastcgiexample/) 
is communicating with `hellophp` within the Cloud Run multi-container service context is to navigate and read through `./nginx/nginx.conf`.

### Build & depoly manually

The following is what is happening within the `Makefile` of this sample.

1. Authenticate in gcloud cli

```bash
gcloud auth login
```

1. Build the `nginx` and `hellophp` container images for our multi-container service.

```bash
# Creating image from the Dockerfile within nginx/ dir.
gcloud builds submit --tag=${REGION}-docker.pkg.dev/${PROJECT_ID}/${REPO_NAME}/nginx ./nginx

# Creating image from the Dockerfile within php-app/ dir.
gcloud builds submit --tag=${REGION}-docker.pkg.dev/${PROJECT_ID}/${REPO_NAME}/php ./php-app
```

1. Deploy the multi-container service

From root directory (`hello-php-nginx-sample/`):

```sh
# Substituting above env vars
sed -i -e s/MC_SERVICE_NAME/${MC_SERVICE_NAME}/g -e s/REGION/${REGION}/g -e s/REPO_NAME/${REPO_NAME} service.yaml

# Deploy your service
gcloud run services replace service.yaml
```

By default, the above command will deploy the following containers into a single service:

* `nginx`: `serving` ingress container (entrypoint)
* `hellophp`: `application` container

The Cloud Run Multi-container service will default access to port `8080`,
where `nginx` container will be listening and proxy request over to `hellophp` container at port `9000`.

## Try it out

Use curl to send an authenticated request:

```bash
curl --header "Authorization: Bearer $(gcloud auth print-identity-token)" <cloud-run-mc-service-url>
```

### Allow unauthenticated requests

To allow un-authenticated access to containers:

```bash
gcloud run services add-iam-policy-binding $MC_SERVICE_NAME \
    --member="allUsers" \
    --role="roles/run.invoker"
```

Visit the Cloud Run url or use curl to send a request:

```bash
curl <cloud-run-mc-service-url>
```


## Find out more:

* https://cloud.google.com/run/docs/deploying#sidecars
* https://cloud.google.com/blog/products/serverless/cloud-run-now-supports-multi-container-deployments
