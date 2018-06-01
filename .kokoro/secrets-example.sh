#!/bin/bash

################################################################################
# Run the following gcloud command to decrypt secrets.sh.enc as follows:       #
#                                                                              #
# gcloud kms decrypt -location=global --keyring=ci --key=ci \                  #
#   --ciphertext-file=.kokoro/secrets.sh.enc \                                 #
#   --plaintext-file=.kokoro/secrets.sh                                        #
#                                                                              #
# Then run `source .kokoro/secrets.sh`                                         #
################################################################################

# General
export GOOGLE_PROJECT_ID=
export GOOGLE_API_KEY=
export GOOGLE_STORAGE_BUCKET=$GOOGLE_PROJECT_ID
export GOOGLE_CLIENT_ID=
export GOOGLE_CLIENT_SECRET=
export GCLOUD_PROJECT=$GOOGLE_PROJECT_ID

# AppEngine
export MAILJET_APIKEY=
export MAILJET_SECRET=
export MAILGUN_APIKEY=
export MAILGUN_DOMAIN=
export MAILGUN_RECIPIENT=
export SENDGRID_APIKEY=
export SENDGRID_SENDER=
export TWILIO_ACCOUNT_SID=
export TWILIO_AUTH_TOKEN=
export TWILIO_FROM_NUMBER=
export TWILIO_TO_NUMBER=

# BigQuery
export GOOGLE_BIGQUERY_DATASET=test_dataset
export GOOGLE_BIGQUERY_TABLE=test_table

# CloudSQL
export CLOUDSQL_CONNECTION_NAME_MYSQL=
export CLOUDSQL_CONNECTION_NAME_POSTGRES=
export CLOUDSQL_DATABASE=
export CLOUDSQL_USER=
export CLOUDSQL_PASSWORD=
export MYSQL_DSN=
export MYSQL_DATABASE=
export MYSQL_USER=
export MYSQL_PASSWORD=
export POSTGRES_DSN=
export POSTGRES_DATABASE=
export POSTGRES_USER=
export POSTGRES_PASSWORD=

# Datastore
export CLOUD_DATASTORE_NAMESPACE=
export DATASTORE_EVENTUALLY_CONSISTENT_RETRY_COUNT=

# DLP
export DLP_TOPIC=dlp-tests
export DLP_SUBSCRIPTION=dlp-tests
export DLP_DEID_WRAPPED_KEY=
export DLP_DEID_KEY_NAME=projects/$GOOGLE_PROJECT_ID/locations/global/keyRings/ci/cryptoKeys/ci

# Firestore
export FIRESTORE_PROJECT_ID=

# IAP
export IAP_CLIENT_ID=
export IAP_PROJECT_ID=
export IAP_PROJECT_NUMBER=
export IAP_URL=

# IAM
export GOOGLE_IAM_USER=

# IOT
export GOOGLE_IOT_DEVICE_CERTIFICATE_B64=

# KMS
export GOOGLE_KMS_KEYRING=
export GOOGLE_KMS_CRYPTOKEY=
export GOOGLE_KMS_CRYPTOKEY_ALTERNATE=
export GOOGLE_KMS_SERVICEACCOUNTEMAIL=

# PubSub
export GOOGLE_PUBSUB_SUBSCRIPTION=php-example-subscription
export GOOGLE_PUBSUB_TOPIC=php-example-topic

# Spanner
export GOOGLE_SPANNER_INSTANCE_ID=
export GOOGLE_SPANNER_DATABASE_ID=test-database

# Tasks
export CLOUD_TASKS_APPENGINE_QUEUE=
export CLOUD_TASKS_LOCATION=
export CLOUD_TASKS_PULL_QUEUE=

# WordPress
export WORDPRESS_DB_INSTANCE_NAME=
export WORDPRESS_DB_USER=$CLOUDSQL_USER
export WORDPRESS_DB_PASSWORD=$CLOUDSQL_PASSWORD
