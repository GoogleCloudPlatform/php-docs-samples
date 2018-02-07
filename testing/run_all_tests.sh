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

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# export the secrets
if [ -f ${GOOGLE_APPLICATION_CREDENTIALS} ]; then
    gcloud config set project ${GOOGLE_PROJECT_ID}
    gcloud auth activate-service-account --key-file \
           "${GOOGLE_APPLICATION_CREDENTIALS}"
    gcloud kms decrypt \
           --location=global \
           --keyring=ci \
           --key=ci \
           --ciphertext-file=${DIR}/export-secrets.sh.enc \
           --plaintext-file=${DIR}/export-secrets.sh
fi


if [ -f ${DIR}/export-secrets.sh ]; then
    source ${DIR}/export-secrets.sh
else
    # We don't have secrets, safer to unset the e2e flags
    unset RUN_DEPLOYMENT_TESTS
    unset RUN_DEVSERVER_TESTS
fi

# only run when explicitly set
if [ "${RUN_CS_FIXER}" = "true" ]; then
  $DIR/run_cs_check.sh;
fi
$DIR/run_test_suite.sh;
# only run for travis crons
if [ "${TRAVIS_EVENT_TYPE}" != "pull_request" ] && \
   [ "${RUN_DEPENDENCY_CHECK}" = "true" ]
then
  $DIR/run_dependency_check.sh;
fi
