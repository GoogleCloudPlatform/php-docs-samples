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

# [START pubsub_update_topic_schema]

use Google\Cloud\PubSub\PubSubClient;

/**
 * Update schema for a topic.
 *
 * @param string $projectId The ID of your Google Cloud Platform project.
 * @param string $topicId The topic id of an existing topic that has schema
 *        settings attached to it.
 * @param string $firstRevisionId The minimum revision id
 * @param string $lastRevisionId The maximum revision id
 */
function update_topic_schema(
    string $projectId,
    string $topicId,
    string $firstRevisionId,
    string $lastRevisionId,
): void {
    $pubsub = new PubSubClient([
        'projectId' => $projectId
    ]);

    $topic = $pubsub->topic($topicId);
    $topic->update([
        'schemaSettings' => [
            // Minimum revision ID
            'firstRevisionId' => $firstRevisionId,
            // Maximum revision ID
            'lastRevisionId' => $lastRevisionId
        ]
    ]);

    printf('Updated topic with schema: %s', $topic->name());
}
# [END pubsub_update_topic_schema]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
