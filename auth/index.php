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
use google\appengine\api\app_identity\AppIdentityService;

// Install composer dependencies with "composer install --no-dev"
// @see http://getcomposer.org for more information.
require __DIR__ . '/vendor/autoload.php';

$onGce = GCECredentials::onGce();
$projectId = $onGce
   ? getenv('GCLOUD_PROJECT')
   : AppIdentityService::getApplicationId();
?>

<h1>Buckets retrieved using the cloud client library:</h1>
<pre>
<?php if ($onGce): ?>
<?php auth_cloud_explicit_compute_engine($projectId) ?>
<?php else: ?>
<?php auth_cloud_explicit_app_engine($projectId) ?>
<?php endif ?>
</pre>

<h1>Buckets retrieved using the api client:</h1>
<pre>
<?php if ($onGce): ?>
<?php auth_api_explicit_compute_engine($projectId) ?>
<?php else: ?>
<?php auth_api_explicit_app_engine($projectId) ?>
<?php endif ?>
</pre>
