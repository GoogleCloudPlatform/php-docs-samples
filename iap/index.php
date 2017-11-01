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

namespace Google\Cloud\Samples\Iap;

use Google\Auth\OAuth2;

// Install composer dependencies with "composer install --no-dev"
// @see http://getcomposer.org for more information.
require __DIR__ . '/vendor/autoload.php';

# Test script for Identity-Aware Proxy code samples.

# The hostname of an application protected by Identity-Aware Proxy.
# When a request is made to https://${JWT_REFLECT_HOSTNAME}/, the
# application should respond with the value of the
# X-Goog-Authenticated-User-JWT (and nothing else.) The
# app_engine_app/ subdirectory contains an App Engine standard
# environment app that does this.
# The project must have the service account used by this test added as a
# member of the project.
$HOSTNAME = 'YOUR_HOSTNAME';
$IAP_CLIENT_ID = ('YOUR_CLIENT_ID');
$SERVICE_ACCOUNT_PATH = 'service-account.json';
$PROJECT_NUMBER = 1;
$PROJECT_ID = 'YOUR_PROJECT_ID';


# JWTs are obtained by IAP-protected applications whenever an
# end-user makes a request.  We've set up an app that echoes back
# the JWT in order to expose it to this test.  Thus, this test
# exercises both make_iap_request and validate_jwt.
$response = make_iap_request($HOSTNAME, $IAP_CLIENT_ID, $SERVICE_ACCOUNT_PATH);
$iap_jwt = explode(': ', (string)$response->getBody())[1];
validate_jwt_from_app_engine($iap_jwt, $PROJECT_NUMBER, $PROJECT_ID);