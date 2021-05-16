<?php
/**
 * Copyright 2021 Google LLC
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

# [START pubsub_get_schema]
use Google\Cloud\PubSub\PubSubClient;

/**
 * Get a schema.
 *
 * @param string $projectId
 * @param string $schemaId
 */
function get_schema($projectId, $schemaId)
{
    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);

    $schema = $pubsub->schema($schemaId);
    $schema->info();

    printf('Schema %s retrieved', $schema->name());
}
# [END pubsub_get_schema]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
