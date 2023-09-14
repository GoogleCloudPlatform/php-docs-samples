# Copyright 2023 Google LLC
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#    https://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

# [START cloudrun_hello_mc_nginx_app_dockerfile]

# Context: ../

# Read more about context: https://docs.docker.com/build/building/context/
# We assume here that required file paths are getting copied
# from the perspective of this directory's parent.

FROM php:8-fpm-alpine

# Configure PHP for Cloud Run.
# Precompile PHP code with opcache.
RUN docker-php-ext-install -j "$(nproc)" opcache
RUN set -ex; \
  { \
    echo "; Cloud Run enforces memory & timeouts"; \
    echo "memory_limit = -1"; \
    echo "; Configure Opcache for Containers"; \
    echo "opcache.enable = 1"; \
    echo "opcache.validate_timestamps = 0"; \
    echo "opcache.memory_consumption = 192"; \
    echo "opcache.max_accelerated_files = 10000"; \
    echo "opcache.max_wasted_percentage = 10"; \
    echo "opcache.interned_strings_buffer = 16";\
  } > "$PHP_INI_DIR/conf.d/cloud-run.ini"

COPY . /var/www/html/

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# [END cloudrun_hello_mc_nginx_app_dockerfile]
