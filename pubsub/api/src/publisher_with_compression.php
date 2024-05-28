<?php
/**
 * Copyright 2024 Google LLC
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

use Google\Cloud\PubSub\MessageBuilder;
use Google\Cloud\PubSub\PubSubClient;

# [START pubsub_publisher_with_compression]

/**
 * Publish a message for a Pub/Sub topic with compression enabled.
 *
 * @param string $projectId  The Google project ID.
 * @param string $topicName  The Pub/Sub topic name.
 * @param string $message  The message to publish.
 */
function publisher_with_compression(
    string $projectId,
    string $topicName,
    string $message
): void {
    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);

    // Enable compression and configure the compression threshold to
    // 10 bytes (default to 240 B). Publish requests of sizes > 10 B
    // (excluding the request headers) will get compressed.
    $topic = $pubsub->topic(
        $topicName,
        [
            'enableCompression' => true,
            'compressionBytesThreshold' => 10
        ]
    );
    $result = $topic->publish((new MessageBuilder)->setData($message)->build());

    printf(
        'Published a compressed message of message ID: %s' . PHP_EOL,
        $result['messageIds'][0]
    );
}
# [END pubsub_publisher_with_compression]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
