#!/bin/bash

# Copyright 2017 Google Inc.
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#      http://www.apache.org/licenses/LICENSE-2.0
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

# Kokoro directory for running these samples
cd github/php-docs-samples

export GOOGLE_APPLICATION_CREDENTIALS=$KOKORO_GFILE_DIR/service-account.json
if [ -n "$GOOGLE_ALT_CREDENTIALS_FILENAME" ]; then
  export GOOGLE_ALT_APPLICATION_CREDENTIALS=$KOKORO_GFILE_DIR/$GOOGLE_ALT_CREDENTIALS_FILENAME
fi

export PATH="$PATH:/opt/composer/vendor/bin:/root/google-cloud-sdk/bin"

# export the secrets
if [ -f ${GOOGLE_APPLICATION_CREDENTIALS} ]; then
    gcloud auth activate-service-account \
        --key-file "${GOOGLE_APPLICATION_CREDENTIALS}" \
        --project $(cat "${GOOGLE_APPLICATION_CREDENTIALS}" | jq -r .project_id)
    gcloud kms decrypt \
           --location=global \
           --keyring=ci \
           --key=ci \
           --ciphertext-file=.kokoro/secrets.sh.enc \
           --plaintext-file=.kokoro/secrets.sh
fi

# Unencrypt and extract secrets
source .kokoro/secrets.sh

mkdir -p build/logs

export PULL_REQUEST_NUMBER=$KOKORO_GITHUB_PULL_REQUEST_NUMBER

# Run code standards check when appropriate
if [ "${RUN_CS_CHECK}" = "true" ]; then
  bash testing/run_cs_check.sh
fi

# If we are running REST tests, disable gRPC
if [ "${RUN_REST_TESTS_ONLY}" = "true" ]; then
  GRPC_INI=$(php -i | grep grpc.ini | sed 's/^Additional .ini files parsed => //g' | sed 's/,*$//g' )
  mv $GRPC_INI "${GRPC_INI}.disabled"
fi

# Install global test dependencies
composer install -d testing/

# Run tests
bash testing/run_test_suite.sh
