<?php
/**
 * Copyright 2026 Google LLC
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

# [START pubsub_create_topic_with_smt]
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\Topic;

/**
 * Create a topic with a Single Message Transform function
 *
 * @param string $projectId
 * @param string $topicId
 */
function create_topic_with_smt(
    string $projectId,
    string $topicId
) {
    $pubsub = new PubSubClient([
        'projectId' => $projectId
    ]);

    $functionName = 'toUpper';
    $code = 'function toUpper(message, metadata){
        message.data = message.data.toUpperCase();
        return message;
    }';

    $smtConfig = [
        'javascriptUdf' => [
            'functionName' => $functionName,
            'code' => $code
        ]
    ];

    $topicConfig = [
        'messageTransforms' => [
            $smtConfig
        ]
    ];

    $topic = $pubsub->createTopic($topicId, $topicConfig);

    printf('Topic with SMT %s created', $topic->name());
}
# [END pubsub_create_topic_with_smt]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
