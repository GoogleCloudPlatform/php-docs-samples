# Google Cloud Endpoints & PHP

This sample demonstrates how to use Google Cloud Endpoints using PHP.

For a complete walkthrough showing how to run this sample in different
environments, see the
[Google Cloud Endpoints Quickstarts](https://cloud.google.com/endpoints/docs/quickstarts).

This sample consists of two parts:

1. The backend
2. The clients

## Running locally

### Running the backend

Install all the dependencies:

    $ composer install

Run the application:

    $ php -S localhost:8080

### Using the echo client

With the app running locally, you can execute the simple echo client using:

    $ php endpoints.php make-request http://localhost:8080 APIKEY

The `APIKEY` can be any string as the local endpoint proxy doesn't need authentication.

## Deploying to Production

See the
[Google Cloud Endpoints Quickstarts](https://cloud.google.com/endpoints/docs/quickstarts).

### Using the echo client

With the project deployed, you'll need to create an API key to access the API.

1. Open the Credentials page of the API Manager in the [Cloud Console](https://console.cloud.google.com/apis/credentials).
2. Click 'Create credentials'.
3. Select 'API Key'.
4. Choose 'Server Key'

With the API key, you can use the echo client to access the API:

    $ php endpoints.php make-request https://YOUR-PROJECT-ID.appspot.com YOUR-API-KEY

### Using the JWT client.

The JWT client demonstrates how to use service accounts to authenticate to
endpoints. To use the client, you'll need both an API key (as described in the
echo client section) and a service account. To create a service account:

1. Open the Credentials page of the API Manager in the [Cloud Console](https://console.cloud.google.com/apis/credentials).
2. Click 'Create credentials'.
3. Select 'Service account key'.
4. In the 'Select service account' dropdown, select 'Create new service account'.
5. Choose 'JSON' for the key type.
6. Click on your newly created service account credentials and then click the
   'Download JSON' button to download a json file with your credentials. You
   will use this later.

To use the service account for authentication:

1. Update `YOUR-SERVICE-ACCOUNT-EMAIL` with your service account's email address
   in `openapi.yaml` (if you're using GKE or GCE) or `openapi-appengine.yaml`
   (if you're using App Engine Flex).

        google_jwt:
          # Update this with your service account's email address.
          x-google-jwks_uri: "https://www.googleapis.com/service_accounts/v1/jwk/YOUR-SERVICE-ACCOUNT-EMAIL"

2. Redeploy your application.

        gcloud app deploy

Now you can use the JWT client to make requests to the API:

    $ php endpoints.php make-request https://YOUR-PROJECT-ID.appspot.com YOUR-API-KEY /path/to/service-account.json

### Using the ID Token client.

The ID Token client demonstrates how to use user credentials to authenticate to endpoints. To use the client, you'll need both an API key (as described in the echo client section) and a OAuth2 client ID. To create a client ID:

1. Open the Credentials page of the API Manager in the [Cloud Console](https://console.cloud.google.com/apis/credentials).
2. Click 'Create credentials'.
3. Select 'OAuth client ID'.
4. Choose 'Other' for the application type.
5. Click on your newly created client credentials and then click the 'Download JSON'
   button to download a json file with your credentials. You will use this later.

To use the client ID for authentication:

1. Update `YOUR-CLIENT-ID` in with your client ID in `openapi.yaml` (if you're
   using GKE or GCE) or `openapi-appengine.yaml` (if you're using App Engine
   Flex).

        google_id_token:
          # Your OAuth2 client's Client ID must be added here. You can add
          # multiple client IDs to accept tokens from multiple clients.
          x-google-jwks_uri: "YOUR-CLIENT-ID"

2. Redeploy your application.

        gcloud app deploy

Now you can use the client ID to make requests to the API:

    $ php endpoints.php make-request https://YOUR-PROJECT-ID.appspot.com YOUR-API-KEY /path/to/client-secrets.json


If you experience any issues, try running `gcloud endpoints configs describe` to
debug the service:

    gcloud endpoints configs describe YOUR-CONFIG-ID --service=YOUR-PROJECT-ID.appspot.com


## Viewing the Endpoints graphs

By using Endpoints, you get access to several metrics that are displayed graphically in the Cloud Console.

To view the Endpoints graphs:

1. Go to the [Endpoints section in Cloud Console](https://console.cloud.google.com/endpoints) of the project you deployed your API to.
2. Click on your API to view more detailed information about the metrics collected.

## Swagger UI

The Swagger UI is an open source Swagger project that allows you to explore your API through a UI. Find out more about it on the [Swagger site](http://swagger.io/swagger-ui/).
