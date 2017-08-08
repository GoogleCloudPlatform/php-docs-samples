<?php
/**
 * Copyright 2017 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Samples\Auth;

use Google\Auth\Credentials\GCECredentials;

// Install composer dependencies with "composer install"
// @see http://getcomposer.org for more information.
require __DIR__ . '/vendor/autoload.php';

print('<pre>');
if (GCECredentials::onGce()) {
    printf("Buckets retrieved using the cloud client library:\n");
    auth_cloud_explicit_compute_engine(getenv('GCLOUD_PROJECT'));
    printf("\n");
    printf("Buckets retrieved using the api client:\n");
    auth_api_explicit_compute_engine(getenv('GCLOUD_PROJECT'));
} else {
    printf("Buckets retrieved using the cloud client library:\n");
    auth_cloud_explicit_app_engine(getenv('GCLOUD_PROJECT'));
    printf("\n");
    printf("Buckets retrieved using the api client:\n");
    auth_api_explicit_app_engine(getenv('GCLOUD_PROJECT'));
}
print('</pre>');



