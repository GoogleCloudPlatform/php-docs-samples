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

cd github/php-docs-samples

# Unencrypt and extract secrets
source ${KOKORO_GFILE_DIR}/secrets.sh

mkdir -p build/logs

export IS_PULL_REQUEST=$KOKORO_GITHUB_PULL_REQUEST_COMMIT

# Run tests
bash testing/run_test_suite.sh
