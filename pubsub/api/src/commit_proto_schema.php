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

# [START pubsub_commit_proto_schema]

use Google\Cloud\Core\Exception\NotFoundException;
use Google\Cloud\PubSub\PubSubClient;

/**
 * Commit a new Proto schema revision to an existing schema.
 *
 * @param string $projectId The ID of your Google Cloud project.
 * @param string $schemaId The ID of the schema to commit.
 * @param string $protoFile The path to the Proto schema file.
 * @return void
 */
function commit_proto_schema(string $projectId, string $schemaId, string $protoFile): void
{
    $client = new PubSubClient([
        'projectId' => $projectId
    ]);

    try {
        $schema = $client->schema($schemaId);
        $definition = file_get_contents($protoFile);
        $info = $schema->commit($definition, 'PROTOCOL_BUFFER');

        printf("Committed a schema using a Protocol Buffer schema: %s\n", $info['name']);
    } catch (NotFoundException $e) {
        printf("%s does not exist.\n", $schemaId);
    }
}
# [END pubsub_commit_proto_schema]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
