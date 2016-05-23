# Copyright 2016 Google Inc. All Rights Reserved.
#
# Licensed under the Apache License, Version 2.0 (the "License");
# you may not use this file except in compliance with the License.
# You may obtain a copy of the License at
#
#    http://www.apache.org/licenses/LICENSE-2.0
#
# Unless required by applicable law or agreed to in writing, software
# distributed under the License is distributed on an "AS IS" BASIS,
# WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
# See the License for the specific language governing permissions and
# limitations under the License.

# This file will go away once gcloud implements fingerprinting.
FROM gcr.io/php-mvm-a/php-nginx:latest

# The docker image will configure the document root according to this
# environment variable.
ENV DOCUMENT_ROOT /app/web

# add write permissions to var directory for cache, logs, and session
RUN mkdir -p /app/var
RUN chmod -R ug+w /app/var
