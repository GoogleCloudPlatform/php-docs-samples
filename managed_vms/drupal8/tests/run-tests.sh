#!/usr/bin/env bash
set -e

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
SAMPLE_DIR="${DIR}/.."
DRUPAL_DIR="${SAMPLE_DIR}/drupal8.test"

VARS=(
    GOOGLE_PROJECT_ID
    GOOGLE_VERSION_ID
    DRUPAL_ADMIN_USERNAME
    DRUPAL_ADMIN_PASSWORD
    DRUPAL_DATABASE_NAME
    DRUPAL_DATABASE_USER
    DRUPAL_DATABASE_PASS
)

# Check for necessary envvars.
PREREQ="true"
for v in "${VARS[@]}"; do
    if [ -z "${!v}" ]; then
        echo "Please set ${v} envvar."
        PREREQ="false"
    fi
done

# Exit when any of the necessary envvar is not set.
if [ "${PREREQ}" = "false" ]; then
    exit 1
fi

# Install drupal console
if [ ! -e ${HOME}/bin/drupal ]; then
    curl https://drupalconsole.com/installer -L -o drupal
    chmod +x drupal
    mv drupal "${HOME}/bin/drupal"
fi

# cleanup installation dir
rm -Rf $DRUPAL_DIR
INSTALL_FILE="${DIR}/config/install_drupal8.yml"

cp "${INSTALL_FILE}.dist" $INSTALL_FILE
sed -i -e "s/@@DRUPAL_DATABASE_NAME@@/${DRUPAL_DATABASE_NAME}/" $INSTALL_FILE
sed -i -e "s/@@DRUPAL_DATABASE_USER@@/${DRUPAL_DATABASE_USER}/" $INSTALL_FILE
sed -i -e "s/@@DRUPAL_DATABASE_PASS@@/${DRUPAL_DATABASE_PASS}/" $INSTALL_FILE
sed -i -e "s/@@DRUPAL_DATABASE_HOST@@/${DRUPAL_DATABASE_HOST}/" $INSTALL_FILE
sed -i -e "s/@@DRUPAL_ADMIN_USERNAME@@/${DRUPAL_ADMIN_USERNAME}/" $INSTALL_FILE
sed -i -e "s/@@DRUPAL_ADMIN_PASSWORD@@/${DRUPAL_ADMIN_PASSWORD}/" $INSTALL_FILE

# download and install
drupal init --root=$DIR
drupal chain --file=$INSTALL_FILE

cd $DRUPAL_DIR

# run some setup commands
drupal theme:download bootstrap 8.x-3.0-beta2
drupal cache:rebuild all

## Perform steps outlined in the README ##

# Copy configuration files to the drupal project
cp $SAMPLE_DIR/{app.yaml,php.ini,Dockerfile,nginx-app.conf} $DRUPAL_DIR

# Deploy to gcloud
gcloud preview app deploy \
  --no-promote --quiet --stop-previous-version --force \
  --project=${GOOGLE_PROJECT_ID} \
  --version=${GOOGLE_VERSION_ID}

# perform the test
curl -vf https://${GOOGLE_VERSION_ID}-dot-${GOOGLE_PROJECT_ID}.appspot.com
