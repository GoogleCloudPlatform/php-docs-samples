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
    ./php-cs-fixer fix --dry-run --diff --config-file=.php_cs .
fi

DIRS=(
    appengine/standard/cloudsql
    appengine/standard/http
    appengine/standard/logging
    appengine/standard/mailgun
    appengine/standard/mailjet
    appengine/standard/memcache
    appengine/standard/phpmyadmin
    appengine/standard/storage
    appengine/standard/taskqueue
    appengine/standard/users
    appengine/wordpress
    bigquery/api
    compute/logging
    datastore
    pubsub
    storage/api
)

for DIR in "${DIRS[@]}"; do
  pushd ${DIR}
  composer install
  phpunit
  if [ -f build/logs/clover.xml ]; then
      cp build/logs/clover.xml \
          ${TEST_BUILD_DIR}/build/logs/clover-${DIR//\//_}.xml
  fi
  popd
done

# run tests that needs special envvars
# run modules API tests
pushd appengine/standard/modules
composer install
env LOCAL_TEST_TARGETS='app.yaml backend.yaml' phpunit
if [ -f build/logs/clover.xml ]; then
    cp build/logs/clover.xml \
        ${TEST_BUILD_DIR}/build/logs/clover-appengine_standard_modules.xml
fi
popd
