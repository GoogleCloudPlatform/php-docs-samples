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

# [START cloudrun_hello_mc_nginx_dockerfile]

# Context: ./
# Read more about context: https://docs.docker.com/build/building/context/
# We assume here that required file paths are getting copied
# from the perspective of this directory.

FROM nginx:1-alpine

COPY ./nginx.conf /etc/nginx/conf.d

COPY index.php /var/www/html/index.php

# [END cloudrun_hello_mc_nginx_dockerfile]
