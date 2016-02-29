#!/usr/bin/env bash
set -e

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
SAMPLE_DIR="${DIR}/.."
SYMFONY_DIR="${DIR}/symfony"

VARS=(
    GOOGLE_PROJECT_ID
    SYMFONY_DATABASE_HOST
    SYMFONY_DATABASE_NAME
    SYMFONY_DATABASE_USER
    SYMFONY_DATABASE_PASS
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

# cleanup installation dir
rm -Rf $SYMFONY_DIR

# install symfony
composer create-project --no-scripts symfony/framework-standard-edition:^3.0 $SYMFONY_DIR

# set up the parameters file
PARAMETERS_FILE="${SYMFONY_DIR}/app/config/parameters.yml"
cp "${PARAMETERS_FILE}.dist" $PARAMETERS_FILE

sed -i -e "s/database_host: .*/database_host: ${SYMFONY_DATABASE_HOST}/" $PARAMETERS_FILE
sed -i -e "s/database_name: .*/database_name: ${SYMFONY_DATABASE_NAME}/" $PARAMETERS_FILE
sed -i -e "s/database_user: .*/database_user: ${SYMFONY_DATABASE_USER}/" $PARAMETERS_FILE
sed -i -e "s/database_password: .*/database_password: ${SYMFONY_DATABASE_PASS}/" $PARAMETERS_FILE

## Perform steps outlined in the README ##

# Copy configuration files to user home directory:
cp "${SAMPLE_DIR}/app.yaml" "${SYMFONY_DIR}/app.yaml"
cp "${SAMPLE_DIR}/nginx-app.conf" "${SYMFONY_DIR}/nginx-app.conf"
cp "${SAMPLE_DIR}/Dockerfile" "${SYMFONY_DIR}/Dockerfile"
cp "${SAMPLE_DIR}/php.ini" "${SYMFONY_DIR}/php.ini"

cd $SYMFONY_DIR

# remove composer.json - this is causing problems right now
rm composer.json

# Deploy to gcloud
if [ -z "${GOOGLE_VERSION_ID}" ]; then
    GOOGLE_VERSION_ID=$(date +%s)
fi

gcloud preview app deploy \
  --no-promote --quiet --stop-previous-version --force --docker-build=remote \
  --project=${GOOGLE_PROJECT_ID} \
  --version=${GOOGLE_VERSION_ID}

# perform the test
curl -fs https://${GOOGLE_VERSION_ID}-dot-${GOOGLE_PROJECT_ID}.appspot.com > /dev/null
