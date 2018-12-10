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

set -ex

# Kokoro directory for running these samples
cd github/php-docs-samples

export GOOGLE_APPLICATION_CREDENTIALS=$KOKORO_GFILE_DIR/service-account.json
export GOOGLE_ALT_APPLICATION_CREDENTIALS=$KOKORO_GFILE_DIR/$GOOGLE_ALT_CREDENTIALS_FILENAME

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

export IS_PULL_REQUEST=$KOKORO_GITHUB_PULL_REQUEST_COMMIT

# Run tests
bash testing/run_test_suite.sh

# Run code standards check on latest version of PHP
if [ ! -z "${IS_PULL_REQUEST}" ] && \
   [ "${RUN_CS_CHECK}" = "true" ]
then
  bash testing/run_cs_check.sh
fi
