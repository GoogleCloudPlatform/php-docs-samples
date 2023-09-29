#!/bin/bash
# Copyright 2023 Google Inc.
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

if [ "${BASH_DEBUG}" = "true" ]; then
    set -x
fi

if [ "${TEST_DIRECTORIES}" = "" ]; then
  TEST_DIRECTORIES="*"
fi

SKIP_DIRS=(
  dialogflow
  iot
)

TMP_REPORT_DIR=$(mktemp -d)
SUCCEEDED_FILE=${TMP_REPORT_DIR}/succeeded
FAILED_FILE=${TMP_REPORT_DIR}/failed
SKIPPED_FILE=${TMP_REPORT_DIR}/skipped

# Determine all files changed on this branch
# (will be empty if running from "main").
FILES_CHANGED=$(git diff --name-only HEAD origin/main)

# If the file RUN_ALL_TESTS is modified, or if we were not triggered from a Pull
# Request, run the whole test suite.
if [ -z "$PULL_REQUEST_NUMBER" ]; then
    RUN_ALL_TESTS=1
else
    labels=$(curl "https://api.github.com/repos/GoogleCloudPlatform/php-docs-samples/issues/$PULL_REQUEST_NUMBER/labels")

    # Check to see if the repo includes the "kokoro:run-all" label
    if  grep -q "kokoro:run-all" <<< $labels; then
        RUN_ALL_TESTS=1
    else
        RUN_ALL_TESTS=0
    fi
fi

for dir in $(find $TEST_DIRECTORIES -type d -name src -not -path '/*'  -not -path 'appengine/*' -not -path '*/vendor/*' -exec dirname {} \;);
do
    # Only run tests for samples that have changed.
    if [ "$RUN_ALL_TESTS" -ne "1" ]; then
        if ! grep -q ^$dir <<< "$FILES_CHANGED" ; then
            echo "Skipping tests in $dir (unchanged)"
            echo "$dir: skipped" >> "${SKIPPED_FILE}"
            continue
        fi
    fi
    if [[ " ${SKIP_DIRS[@]} " =~ " ${dir} " ]]; then
        printf "Skipping $dir (explicitly flagged to be skipped)\n\n"
        echo "$dir: skipped" >> "${SKIPPED_FILE}"
        continue
    fi
    composer update --working-dir=$dir --ignore-platform-reqs -q
    echo "<?php require_once 'testing/sample_helpers.php';require_once '$dir/vendor/autoload.php';" > autoload.php
    neon="testing/phpstan/default.neon.dist"
    if [ -f "testing/phpstan/$dir.neon.dist" ]; then
        neon="testing/phpstan/$dir.neon.dist"
    fi
    echo "Running phpstan in \"$dir\" with config \"$neon\""
    testing/vendor/bin/phpstan analyse $dir/src \
        --autoload-file=autoload.php \
        --configuration=$neon
    if [ $? == 0 ]; then
        echo "$dir: ok" >> "${SUCCEEDED_FILE}"
    else
        echo "$dir: failed" >> "${FAILED_FILE}"
    fi
done

set +x

if [ -f "${SUCCEEDED_FILE}" ]; then
    echo "--------- Succeeded -----------"
    cat "${SUCCEEDED_FILE}"
    echo "-------------------------------"
fi

if [ -f "${SKIPPED_FILE}" ]; then
    echo "--------- SKIPPED --------------"
    cat "${SKIPPED_FILE}"
    echo "--------------------------------"
    # Report any skips
fi

if [ -f "${FAILED_FILE}" ]; then
    echo "--------- Failed --------------"
    cat "${FAILED_FILE}"
    echo "-------------------------------"
    # Report any failure
    exit 1
fi
