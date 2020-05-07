#!/usr/bin/env bash

# Copyright 2020 Google LLC
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

# Find all directories containing composer.json.
directories=$(find . -name "composer.json" -not -path "**/vendor/*" -exec dirname {} \;)

# Update dependencies in all directories containing composer.json.
for SAMPLE_DIR in $directories; do
  printf '\n### Checking dependencies in %s ###\n', "$SAMPLE_DIR"
  pushd "$SAMPLE_DIR"
  composer install --ignore-platform-reqs --no-dev

  updatePackages=()
  outdatedPackages=$(echo \
    "$(composer outdated 'google/*' --direct --format=json | jq '.installed' 2>/dev/null) $(composer outdated 'firebase/*' --direct --format=json | jq '.installed' 2>/dev/null)" \
    | jq -s add)

  if [[ "$outdatedPackages" != "null" ]] && [[ "$outdatedPackages" != "[]" ]] ; then
    count=$(echo "$outdatedPackages" | jq length)

    for (( i = 0; i < count; i++ ))
    do
      name=$(echo "$outdatedPackages" | jq -r --arg i "$i" '.[$i | tonumber].name')
      version=$(echo "$outdatedPackages" | jq -r --arg i "$i" '.[$i | tonumber].latest' | sed -e 's/^v//')
      if [[ "${version:0:4}" != dev- ]]; then
        updatePackages+=( "$name:^$version" )
      fi
    done

    if [ ${#updatePackages[@]} -gt 0 ]; then
      composer require --ignore-platform-reqs --update-no-dev --update-with-dependencies "${updatePackages[@]}"
    fi
  fi

  popd
done
