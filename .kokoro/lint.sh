#!/bin/bash

# Copyright 2021 Google Inc.
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

mkdir -p build/logs

export PULL_REQUEST_NUMBER=$KOKORO_GITHUB_PULL_REQUEST_NUMBER

# Run code standards check when appropriate
if [ "${RUN_CS_CHECK}" = "true" ]; then
  bash testing/run_cs_check.sh
fi
