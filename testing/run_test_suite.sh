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

# run php-cs-fixer
if [ "${RUN_CS_FIXER}" = "true" ]; then
    ${HOME}/php-cs-fixer fix --dry-run --diff
fi

# loop through all directories containing "phpunit.xml*" and run them
find * -name 'phpunit.xml*' -not -path '*/vendor/*' -exec dirname {} \; | while read DIR
do
    pushd ${DIR}
    if [ -f "composer.json" ]; then
        composer install
    fi
    phpunit
    if [ -f build/logs/clover.xml ]; then
        cp build/logs/clover.xml \
            ${TEST_BUILD_DIR}/build/logs/clover-${DIR//\//_}.xml
    fi
    popd
done
