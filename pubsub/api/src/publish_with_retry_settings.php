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

# [START pubsub_publisher_retry_settings]
use Google\Cloud\PubSub\MessageBuilder;
use Google\Cloud\PubSub\PubSubClient;

/**
 * Publishes a message for a Pub/Sub topic with Retry Settings.
 *
 * @param string $projectId  The Google project ID.
 * @param string $topicName  The Pub/Sub topic name.
 * @param string $message    The message to publish.
 */
function publish_with_retry_settings($projectId, $topicName, $message)
{
    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);

    $topic = $pubsub->topic($topicName);
    $retrySettings = [
        'initialRetryDelayMillis' => 100,
        'retryDelayMultiplier' => 5,
        'maxRetryDelayMillis' => 60000,
        'initialRpcTimeoutMillis' => 1000,
        'rpcTimeoutMultiplier' => 1,
        'maxRpcTimeoutMillis' => 600000,
        'totalTimeoutMillis' => 600000
    ];
    $topic->publish((new MessageBuilder)->setData($message)->build(), [
        'retrySettings' => $retrySettings
    ]);

    print('Message published with retry settings' . PHP_EOL);
}
# [END pubsub_publisher_retry_settings]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
