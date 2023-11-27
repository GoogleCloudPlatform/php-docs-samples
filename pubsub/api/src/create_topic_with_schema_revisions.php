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

# [START pubsub_create_topic_with_schema_revisions]

use Google\Cloud\PubSub\PubSubClient;

/**
 * Create a topic with schema having it's revisions specified.
 *
 * @param string $projectId The ID of your Google Cloud Platform project.
 * @param string $topicId The topic id.
 * @param string $schemaId The existing schema id.
 * @param string $encoding The encoding for schema
 * @param string $firstRevisionId The minimum revision id
 * @param string $lastRevisionId The maximum revision id
 */
function create_topic_with_schema_revisions(
    string $projectId,
    string $topicId,
    string $schemaId,
    string $encoding,
    string $firstRevisionId,
    string $lastRevisionId,
): void {
    $pubsub = new PubSubClient([
        'projectId' => $projectId
    ]);

    $schema = $pubsub->schema($schemaId);

    $topic = $pubsub->createTopic($topicId, [
        'schemaSettings' => [
            // The schema may be provided as an instance of the schema type,
            // or by using the schema ID directly.
            'schema' => $schema,
            // Encoding may be either `BINARY` or `JSON`.
            // Provide a string or a constant from Google\Cloud\PubSub\V1\Encoding.
            'encoding' => $encoding,
            // Minimum revision ID
            'firstRevisionId' => $firstRevisionId,
            // Maximum revision ID
            'lastRevisionId' => $lastRevisionId
        ]
    ]);

    printf('Created topic with schema: %s', $topic->name());
}
# [END pubsub_create_topic_with_schema_revisions]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
