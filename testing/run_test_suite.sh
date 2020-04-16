#!/bin/bash
# Copyright 2016 Google Inc.
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#     http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

set -e

if [ "${BASH_DEBUG}" = "true" ]; then
    set -x
fi

# directories known as flaky tests
FLAKES=(
    # Add directories here to run the tests but ignore them if they fail
    datastore/api
    jobs
    asset
    dlp
)

# Directories we do not want to run tests in, even if they exist
SKIP_TESTS=(
    appengine/php55/taskqueue
    appengine/php55/wordpress
    appengine/php55/grpc
    appengine/php55/sendgrid
    appengine/php55/mail
    appengine/php55/phpmyadmin
    appengine/php55/mailjet
    appengine/php55/storage
    appengine/php55/memcache
    appengine/php55/http
    appengine/php55/users
    appengine/php55/cloudsql
    appengine/php55/mailgun
    appengine/php55/modules
    appengine/php55/twilio
)

# tests to run with grpc.so disabled
REST_TESTS=(
    asset
    bigquerydatatransfer
    bigtable
    dialogflow
    dlp
    error_reporting
    iot
    monitoring
    video
    vision
)

# These tests run in a different project, determined by GOOGLE_ALT_PROJECT_ID
ALT_PROJECT_TESTS=(
    appengine/flexible/storage
    asset
    bigquery/api
    bigquery/quickstart
    bigtable
    datastore/api
    datastore/tutorial
    dialogflow
    dlp
    error_reporting
    iot
    kms
    logging
    monitoring
    pubsub/api
    storage
    spanner
    video
    vision
)

TMP_REPORT_DIR=$(mktemp -d)

SUCCEEDED_FILE=${TMP_REPORT_DIR}/succeeded
FAILED_FILE=${TMP_REPORT_DIR}/failed
FAILED_FLAKY_FILE=${TMP_REPORT_DIR}/failed_flaky

# Determine all files changed on this branch
# (will be empty if running from "master").
FILES_CHANGED=$(git diff --name-only HEAD $(git merge-base HEAD master))

# If the file RUN_ALL_TESTS is modified, or if we were not triggered from a Pull
# Request, run the whole test suite.
if [ -z "$PULL_REQUEST_NUMBER" ]; then
    RUN_ALL_TESTS=1
else
    # Check to see if the repo includes the "kokoro:run-all" label
    if curl "https://api.github.com/repos/GoogleCloudPlatform/php-docs-samples/issues/$PULL_REQUEST_NUMBER/labels" \
        | grep -q "kokoro:run-all"; then
        RUN_ALL_TESTS=1
    else
        RUN_ALL_TESTS=0
    fi

fi

if [ "${TEST_DIRECTORIES}" = "" ]; then
  TEST_DIRECTORIES="*"
fi

TESTDIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
TESTCMD="$TESTDIR/vendor/bin/phpunit"

if ! type $TESTCMD > /dev/null; then
  echo "run \"composer install -d testing/\" to install testing dependencies"
  exit 1
fi

run_tests()
{
    if [[ " ${ALT_PROJECT_TESTS[@]} " =~ " ${DIR} " ]] && [ ! -z "$GOOGLE_ALT_PROJECT_ID" ]; then
        echo "Using alternate project $GOOGLE_ALT_PROJECT_ID"
        GOOGLE_APPLICATION_CREDENTIALS=$GOOGLE_ALT_APPLICATION_CREDENTIALS \
            GCLOUD_PROJECT=$GOOGLE_ALT_PROJECT_ID \
            GOOGLE_PROJECT_ID=$GOOGLE_ALT_PROJECT_ID \
            GOOGLE_STORAGE_BUCKET=$GOOGLE_ALT_STORAGE_BUCKET \
            $TESTCMD -v
    else
        $TESTCMD -v
    fi
    if [ $? == 0 ]; then
        echo "$1: ok" >> "${SUCCEEDED_FILE}"
    else
        if [[ " ${FLAKES[@]} " =~ " ${DIR} " ]]; then
            echo "$1: failed" >> "${FAILED_FLAKY_FILE}"
        else
            echo "$1: failed" >> "${FAILED_FILE}"
        fi
    fi
}

# Loop through all directories containing "phpunit.xml*" and run the test suites.
find $TEST_DIRECTORIES -name 'phpunit.xml*' -not -path '*vendor/*' -exec dirname {} \; | while read DIR
do
    # Only run tests for samples that have changed.
    if [ "$RUN_ALL_TESTS" -ne "1" ]; then
        if ! grep -q ^$DIR <<< "$FILES_CHANGED" ; then
            echo "Skipping tests in $DIR (unchanged)"
            continue
        fi
    fi
    if [[ " ${SKIP_TESTS[@]} " =~ " ${DIR} " ]]; then
        echo "Skipping tests in $DIR (explicitly flagged to be skipped)"
        continue
    fi
    if [ "${RUN_REST_TESTS_ONLY}" = "true" ] && [[ ! " ${REST_TESTS[@]} " =~ " ${DIR} " ]]; then
        echo "Skipping tests in $DIR (no REST tests)"
        continue
    fi
    if [ "$RUN_DEPLOYMENT_TESTS" != "true" ] &&
       [[ -z $(find $DIR/test/ -type f -name *Test.php -not -name Deploy*Test.php) ]]; then
        echo "Skipping tests in $DIR (Deployment tests only)"
        continue
    fi
    pushd ${DIR}
    mkdir -p build/logs
    # Temporarily allowing error
    set +e
    if [ -f "composer.json" ]; then
        # install composer dependencies
        composer -q install
    fi
    if [ $? != 0 ]; then
        # If the PHP required version is too low, skip the test
        if composer check-platform-reqs | grep "__root__ requires php" | grep failed ; then
            echo "Skipping tests in $DIR (incompatible PHP version)"
        else
            # Run composer without "-q"
            composer install
            echo "${DIR}: failed" >> "${FAILED_FILE}"
        fi
    else
        echo "running phpunit in ${DIR}"
        run_tests $DIR
        set -e
        if [ "$RUN_ALL_TESTS" -eq "1" ] && [ -f build/logs/clover.xml ]; then
            cp build/logs/clover.xml \
                ${TEST_BUILD_DIR}/build/logs/clover-${DIR//\//_}.xml
        fi
    fi
    popd
done

# Show the summary report
set +x

if [ -f "${SUCCEEDED_FILE}" ]; then
    echo "--------- Succeeded tests -----------"
    cat "${SUCCEEDED_FILE}"
    echo "-------------------------------------"
fi

if [ -f "${FAILED_FILE}" ]; then
    echo "--------- Failed tests --------------"
    cat "${FAILED_FILE}"
    echo "-------------------------------------"
fi

if [ -f "${FAILED_FLAKY_FILE}" ]; then
    echo "-------- Failed flaky tests ---------"
    cat "${FAILED_FLAKY_FILE}"
    echo "-------------------------------------"
fi

# Finally report failure if any tests failed
if [ -f "${FAILED_FILE}" ]; then
    exit 1
fi
