#!/usr/bin/env bash
set -e
set -o xtrace

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

cd $SYMFONY_DIR

# Remove composer.json - this is causing problems right now
rm composer.json

## Perform steps outlined in the README ##

# Copy configuration files to the symfony project
cp $SAMPLE_DIR/{app.yaml,php.ini,Dockerfile,nginx-app.conf} $SYMFONY_DIR

# Deploy to a module other than "default"
if [ ! -z "${GOOGLE_MODULE}" ]; then
  echo "module: ${GOOGLE_MODULE}" >> ${SYMFONY_DIR}/app.yaml
fi

# Set a version ID if none was supplied
if [ -z "${GOOGLE_VERSION_ID}" ]; then
    GOOGLE_VERSION_ID=$(date +%s)
fi

# Deploy to gcloud (try 3 times)
attempts=0
until [ $attempts -ge 3 ]
do
  (
    gcloud preview app deploy \
      --no-promote --quiet --stop-previous-version --force --docker-build=remote \
      --project=${GOOGLE_PROJECT_ID} \
      --version=${GOOGLE_VERSION_ID} \
        && break
  ) || true
  attempts=$[$attempts+1]
  sleep 1
done

# Determine the deployed URL
if [ -z "${GOOGLE_MODULE}" ]; then
  VERSION_PREFIX=${GOOGLE_VERSION_ID}
else
  VERSION_PREFIX=${GOOGLE_VERSION_ID}-dot-${GOOGLE_MODULE}
fi

# perform the test
curl -fs https://${VERSION_PREFIX}-dot-${GOOGLE_PROJECT_ID}.appspot.com > /dev/null
