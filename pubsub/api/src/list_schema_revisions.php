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

use Google\Cloud\Core\Exception\NotFoundException;
use Google\Cloud\PubSub\PubSubClient;

# [START pubsub_list_schema_revisions]

/**
 * Lists all schema revisions for the named schema.
 *
 * @param string $projectId The ID of your Google Cloud Platform project.
 * @param string $schemaId The ID of the schema.
 * @return void $name
 */
function list_schema_revisions(string $projectId, string $schemaId): void
{
    $client = new PubSubClient([
        'projectId' => $projectId
    ]);

    try {
        $schema = $client->schema($schemaId);
        $revisions = $schema->listRevisions();
        foreach ($revisions['schemas'] as $revision) {
            printf('Got a schema revision: %s' . PHP_EOL, $revision['revisionId']);
        }
        print('Listed schema revisions.' . PHP_EOL);
    } catch (NotFoundException $ex) {
        printf('%s not found' . PHP_EOL, $schemaId);
    }
}
# [END pubsub_list_schema_revisions]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
