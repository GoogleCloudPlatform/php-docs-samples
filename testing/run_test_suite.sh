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
    datastore/api
)

# tests to run with grpc.so disabled
REST_TESTS=(
    bigquerydatatransfer
    error_reporting
    dialogflow
    dlp
    monitoring
    video
)

GRPC_INI=$(php -i | grep grpc.ini | sed 's/,*$//g')

TMP_REPORT_DIR=$(mktemp -d)

SUCCEEDED_FILE=${TMP_REPORT_DIR}/succeeded
FAILED_FILE=${TMP_REPORT_DIR}/failed
FAILED_FLAKY_FILE=${TMP_REPORT_DIR}/failed_flaky

# Determine all files changed on this branch
# (will be empty if running from "master").
FILES_CHANGED=$(git diff --name-only HEAD $(git merge-base HEAD master))

# If any files outside the sample directories changed, or if we are not
# on a Pull Request, run the whole test suite.
if grep -q ^testing\/ <<< "$FILES_CHANGED" || \
   grep -q ^.kokoro\/ <<< "$FILES_CHANGED" || \
    grep -qv \/ <<< "$FILES_CHANGED" || \
    [ -z "$IS_PULL_REQUEST" ]; then
    RUN_ALL_TESTS=1
else
    RUN_ALL_TESTS=0
fi

if [ "${TEST_DIRECTORIES}" = "" ]; then
  TEST_DIRECTORIES="*"
fi

run_tests()
{
    if [ -f "vendor/bin/phpunit" ]; then
        vendor/bin/phpunit -v
    else
        phpunit -v
    fi
    if [ $? == 0 ]; then
        echo "$1: ok" >> "${SUCCEEDED_FILE}"
    else
        if [[ "${FLAKES[@]}" =~ "${DIR}" ]]; then
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
            echo "Skipping tests in $DIR\n"
            continue
        fi
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
        # Run composer without "-q"
        composer install
        echo "${DIR}: failed" >> "${FAILED_FILE}"
    else
        echo "running phpunit in ${DIR}"
        run_tests $DIR
        if [[ "${REST_TESTS[@]}" =~ "${DIR}" ]]; then
            # disable gRPC to test using REST only, then re-enable it
            mv $GRPC_INI "${GRPC_INI}.disabled"
            run_tests "${DIR} (rest)"
            mv "${GRPC_INI}.disabled" $GRPC_INI
        fi
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
