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
PHP_CS_FIXER="php-cs-fixer"
if [ -f "vendor/bin/php-cs-fixer" ]; then
    PHP_CS_FIXER="vendor/bin/php-cs-fixer"
elif [ -f "./php-cs-fixer" ]; then
    PHP_CS_FIXER="./php-cs-fixer"
fi

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
$PHP_CS_FIXER fix --dry-run --diff --config="$DIR/../.php_cs.dist" --path-mode=intersection .
