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

# Loop through all directories containing "phpunit.xml*" and run the test suites.
find * -name 'phpunit.xml*' -not -path '*vendor/*' -exec dirname {} \; | while read DIR
do
    pushd ${DIR}
    if [ -f "composer.json" ]; then
        # install composer dependencies
        composer install
        # verify direct google dependencies are up to date
        if composer outdated --direct | grep -q 'google/' ; then
            # save out-of-date libraries
            OUTPUT=$(composer outdated --direct | grep 'google/')
            DEPS=$DEPS$'\n'$DIR$':\n'$OUTPUT$'\n'
        fi
    fi
    popd
done

if [ ! -e $DEPS ]; then
    # Exit and display all deps needing an update.
    echo "Some dependencies are out of date in \"$DIR\""
    echo "run \"testing/run_dependency_update.sh\" to update them"
    echo $DEPS
    exit 1
fi
