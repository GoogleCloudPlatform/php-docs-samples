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
if [ -f "${GOOGLE_APPLICATION_CREDENTIALS}" ]; then
    PROJECT_ID=$(jq -r .project_id "${GOOGLE_APPLICATION_CREDENTIALS}")
    if ! gcloud auth activate-service-account \
        --key-file "${GOOGLE_APPLICATION_CREDENTIALS}" \
        --project "${PROJECT_ID}"; then
        echo "Primary service account activation failed. Trying alternate..."
        if [ -f "${GOOGLE_ALT_APPLICATION_CREDENTIALS}" ]; then
             if [ -z "${GOOGLE_ALT_PROJECT_ID}" ]; then
                 GOOGLE_ALT_PROJECT_ID=$(jq -r .project_id "${GOOGLE_ALT_APPLICATION_CREDENTIALS}")
             fi
             gcloud auth activate-service-account \
                --key-file "${GOOGLE_ALT_APPLICATION_CREDENTIALS}" \
                --project "${GOOGLE_ALT_PROJECT_ID}"
        else
            echo "No alternate service account available."
            exit 1
        fi
    fi
    if [ -f .kokoro/secrets.sh.enc ]; then
        gcloud kms decrypt \
               --location=global \
               --keyring=ci \
               --key=ci \
               --ciphertext-file=.kokoro/secrets.sh.enc \
               --plaintext-file=.kokoro/secrets.sh
    fi
fi

# Unencrypt and extract secrets
if [ -f .kokoro/secrets.sh ]; then
    source .kokoro/secrets.sh
fi

mkdir -p build/logs

export PULL_REQUEST_NUMBER=$KOKORO_GITHUB_PULL_REQUEST_NUMBER

# If we are running REST tests, disable gRPC
if [ "${RUN_REST_TESTS_ONLY}" = "true" ]; then
  GRPC_INI=$(php -i | grep grpc.ini | sed 's/^Additional .ini files parsed => //g' | sed 's/,*$//g' )
  mv $GRPC_INI "${GRPC_INI}.disabled"
fi

# Install global test dependencies
composer install -d testing/

# Configure the current directory as a safe directory
git config --global --add safe.directory $(pwd)

# Run tests
bash testing/run_test_suite.sh
