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

set -ex

# Determine all files changed on this branch
# (will be empty if running from "master").
FILES_CHANGED=$(git diff --name-only HEAD $(git merge-base HEAD master))

# If any files outside the sample directories changed, or if we are not
# on a Pull Request, run the whole test suite.
if grep -q ^testing\/ <<< "$FILES_CHANGED" || \
    grep -qv \/ <<< "$FILES_CHANGED" || \
    [ -z "$TRAVIS_PULL_REQUEST_BRANCH" ]; then
    RUN_ALL_TESTS=1
else
    RUN_ALL_TESTS=0
fi

if [ "${TEST_DIRECTORIES}" = "" ]; then
  TEST_DIRECTORIES="*"
fi

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
    if [ -f "composer.json" ]; then
        # install composer dependencies
        composer install -q
    fi
    echo "running phpunit in ${DIR}"
    if [ -f "vendor/bin/phpunit" ]; then
        vendor/bin/phpunit
    else
        phpunit
    fi
    if [ "$RUN_ALL_TESTS" -eq "1" ] && [ -f build/logs/clover.xml ]; then
        cp build/logs/clover.xml \
            ${TEST_BUILD_DIR}/build/logs/clover-${DIR//\//_}.xml
    fi
    popd
done
