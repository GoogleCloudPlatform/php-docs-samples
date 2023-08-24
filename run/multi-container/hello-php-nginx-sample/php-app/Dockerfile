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

# The Dockerfile will copy required assets
# from the perspective of a particular directory location,
# which we will call "context".

# Context: ../
# We assume here that required file paths are getting copied
# from the perspective of this directory's parent.

FROM php:8-fpm-alpine

# Use the default production configuration
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN docker-php-ext-install opcache

# COPY php.ini /usr/local/etc/php
COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini

COPY . /var/www/html/

# [END cloudrun_hello_mc_nginx_app_dockerfile]
