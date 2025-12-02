<?php

/**
 * Copyright 2025 Google LLC.
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

# [START pubsub_create_unwrapped_push_subscription]
use Google\Cloud\PubSub\PubSubClient;

/**
 * Creates an unwrapped push subscription.
 *
 * @param string $projectId  The Google project ID.
 * @param string $topicName  The Pub/Sub topic name.
 * @param string $subscriptionId  The ID of the subscription.
 */
function create_unwrapped_push_subscription(
    string $projectId,
    string $topicName,
    string $subscriptionId
): void {

    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);
    $pubsub->subscribe($subscriptionId, $topicName, [
        'pushConfig' => [
            'no_wrapper' => [
                'write_metadata' => true
            ]
        ]
    ]);
    printf('Unwrapped push subscription created: %s' . PHP_EOL, $subscriptionId);
}
# [END pubsub_create_unwrapped_push_subscription]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
