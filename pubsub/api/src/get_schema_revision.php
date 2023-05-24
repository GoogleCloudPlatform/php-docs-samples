<?php
/**
 * Copyright 2023 Google LLC
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/blob/main/pubsub/api/README.md
 */
namespace Google\Cloud\Samples\PubSub;

use Google\ApiCore\ApiException;
use Google\Cloud\PubSub\V1\SchemaServiceClient;

# [START pubsub_get_schema_revision]

/**
 * Gets a schema revision.
 *
 * @param string $projectId The ID of your Google Cloud Platform project.
 * @param string $schemaId The ID of the schema.
 * @param string $schemaRevisionId name of the schema.
 */
function get_schema_revision(string $projectId, string $schemaId, string $schemaRevisionId)
{
    $schemaServiceClient = new SchemaServiceClient();
    $schemaName = $schemaServiceClient->schemaName(
        $projectId, $schemaId . '@' . $schemaRevisionId
    );

    try {
        $response = $schemaServiceClient->getSchema($schemaName);
        printf('Got a schema revision: %s' . PHP_EOL, $response->getName());
    } catch (ApiException $ex) {
        printf('%s not found' . PHP_EOL, $schemaName);
    }
}
# [END pubsub_get_schema_revision]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
