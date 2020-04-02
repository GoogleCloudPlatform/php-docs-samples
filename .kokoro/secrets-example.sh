#!/bin/bash

# This file contains the necessary environment variables for the kokoro 
# tests. Contact the repository owners if you need access to view or modify
# the variables.
# 
# Run the following gcloud command to decrypt secrets.sh.enc as follows:
#
# gcloud kms decrypt --location=global --keyring=ci --key=ci \
#   --ciphertext-file=.kokoro/secrets.sh.enc \
#   --plaintext-file=.kokoro/secrets.sh
#
# Then run `source .kokoro/secrets.sh`
# 
# To modify the file, edit .kokoro/secrets.sh then use the following gcloud 
# command to encrypt it with the changes:
#
# gcloud kms encrypt --location=global --keyring=ci --key=ci \
#   --ciphertext-file=.kokoro/secrets.sh.enc \
#   --plaintext-file=.kokoro/secrets.sh

# General
export GOOGLE_PROJECT_ID=
export GOOGLE_STORAGE_BUCKET=$GOOGLE_PROJECT_ID
export GOOGLE_CLIENT_ID=
export GOOGLE_CLIENT_SECRET=
export GCLOUD_PROJECT=$GOOGLE_PROJECT_ID
# For running tests in separate projects
export GOOGLE_ALT_PROJECT_ID=$GOOGLE_PROJECT_ID

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
export CLOUDSQL_CONNECTION_NAME=$CLOUDSQL_CONNECTION_NAME_MYSQL
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

# Memorystore
export REDIS_HOST=
export REDIS_PORT=

# PubSub
export GOOGLE_PUBSUB_SUBSCRIPTION=php-example-subscription
export GOOGLE_PUBSUB_TOPIC=php-example-topic

# Security Center
export GOOGLE_ORGANIZATION_ID=
export GOOGLE_SECURITYCENTER_PUBSUB_TOPIC=

# Spanner
export GOOGLE_SPANNER_INSTANCE_ID=
export GOOGLE_SPANNER_DATABASE_ID=test-database

# Storage
export GOOGLE_STORAGE_OBJECT=storage/test_data.csv
export GOOGLE_STORAGE_KMS_KEYNAME=projects/$GOOGLE_PROJECT_ID/locations/us/keyRings/$GOOGLE_KMS_KEYRING/cryptoKeys/storage-bucket
export GOOGLE_REQUESTER_PAYS_STORAGE_BUCKET=$GOOGLE_STORAGE_BUCKET

# Tasks
export CLOUD_TASKS_APPENGINE_QUEUE=
export CLOUD_TASKS_LOCATION=
export CLOUD_TASKS_PULL_QUEUE=

# Redislabs Memcache
export MEMCACHE_USERNAME=
export MEMCACHE_PASSWORD=
export MEMCACHE_ENDPOINT=

# WordPress
export WORDPRESS_DB_INSTANCE_NAME=
export WORDPRESS_DB_USER=$CLOUDSQL_USER
export WORDPRESS_DB_PASSWORD=$CLOUDSQL_PASSWORD

# Laravel
export LARAVEL_CLOUDSQL_CONNECTION_NAME=$CLOUDSQL_CONNECTION_NAME_MYSQL
export LARAVEL_DB_DATABASE=laravel
export LARAVEL_DB_USERNAME=$CLOUDSQL_USER
export LARAVEL_DB_PASSWORD=$CLOUDSQL_PASSWORD

# Symfony
export SYMFONY_CLOUDSQL_CONNECTION_NAME=$CLOUDSQL_CONNECTION_NAME_MYSQL
export SYMFONY_DB_DATABASE=symfony
export SYMFONY_DB_USERNAME=$CLOUDSQL_USER
export SYMFONY_DB_PASSWORD=$CLOUDSQL_PASSWORD
