<?php
/**
 * Copyright 2016 Google Inc.
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

/**
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/blob/master/pubsub/api/README.md
 */

namespace Google\Cloud\Samples\PubSub;

/**
 * This file is to be used as an example only!
 *
 * Usage:
 * ```
 * $projectId = 'Your Project ID';
 * $pubsub = require '/path/to/pubsub_client.php';
 * ```
 */
# [START build_service]
use Google\Cloud\PubSub\PubSubClient;

$pubsub = new PubSubClient([
    'projectId' => $projectId,
]);
# [END build_service]
return $pubsub;
