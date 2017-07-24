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

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
# only run when explicitly set
if [ "${RUN_CS_FIXER}" = "true" ]; then
  $DIR/run_cs_check.sh;
fi
$DIR/run_test_suite.sh;
# only run for travis crons
if [ "${TRAVIS_EVENT_TYPE}" != "pull_request" ]; then
  $DIR/run_dependency_check.sh;
fi
