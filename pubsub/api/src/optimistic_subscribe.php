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

# [START pubsub_optimistic_subscribe]
use Google\Cloud\Core\Exception\NotFoundException;
use Google\Cloud\PubSub\PubSubClient;

/**
 * Optimistically subscribes to a topic
 *
 * @param string $projectId  The Google project ID.
 * @param string $topicName  The Pub/Sub topic name.
 * @param string $subscriptionId  The ID of the subscription.
 */
function optimistic_subscribe(
    string $projectId,
    string $topicName,
    string $subscriptionId
): void {

    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);

    $subscription = $pubsub->subscription($subscriptionId);

    try {
        $messages = $subscription->pull();
        foreach ($messages as $message) {
            printf('PubSub Message: %s' . PHP_EOL, $message->data());
            $subscription->acknowledge($message);
        }
    } catch (NotFoundException $e) { // Subscription is not found
        printf('Exception Message: %s' . PHP_EOL, $e->getMessage());
        printf('StackTrace: %s' . PHP_EOL, $e->getTraceAsString());
        // Create subscription and retry the pull. Any messages published before subscription creation would not be received.
        $pubsub->subscribe($subscriptionId, $topicName);
        optimistic_subscribe($projectId, $topicName, $subscriptionId);
    }
}
# [END pubsub_optimistic_subscribe]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
