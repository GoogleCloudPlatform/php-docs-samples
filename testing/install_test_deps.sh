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

install_gcloud()
{
    # Install gcloud
    if [ ! -d ${HOME}/gcloud/google-cloud-sdk ]; then
        mkdir -p ${HOME}/gcloud
        wget \
            https://dl.google.com/dl/cloudsdk/release/google-cloud-sdk.tar.gz \
            --directory-prefix=${HOME}/gcloud
        pushd "${HOME}/gcloud"
        tar xzf google-cloud-sdk.tar.gz
        ./google-cloud-sdk/install.sh \
            --usage-reporting false \
            --path-update false \
            --command-completion false
        popd
    fi
}

configure_gcloud()
{
    # Configure gcloud
    gcloud config set project ${GOOGLE_PROJECT_ID}
    gcloud config set app/promote_by_default false
    gcloud config set app/use_cloud_build true
    if [ -f ${GOOGLE_APPLICATION_CREDENTIALS} ]; then
        gcloud auth activate-service-account --key-file \
            "${GOOGLE_APPLICATION_CREDENTIALS}"
    fi
    gcloud -q components install app-engine-python
    gcloud -q components install app-engine-php
    # pinning to 104.0.0 because 105.0.0 is broken for php app
    gcloud -q components update --version 104.0.0
}

install_php_cs_fixer()
{
    # Install PHP-cs-fixer
    if [ ! -f php-cs-fixer ]; then
        wget http://get.sensiolabs.org/php-cs-fixer.phar -O php-cs-fixer
        chmod a+x php-cs-fixer
    fi
}

if [ "${RUN_DEPLOYMENT_TESTS}" = "true" ] \
    || [ "${RUN_DEVSERVER_TESTS}" = "true" ]; then
    install_gcloud
    configure_gcloud
fi

if [ "${RUN_CS_FIXER}" = "true" ]; then
    install_php_cs_fixer
fi
