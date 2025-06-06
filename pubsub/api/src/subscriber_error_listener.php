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

# [START pubsub_subscriber_error_listener]
use Google\Cloud\PubSub\PubSubClient;

/**
 * Subscribes with an error listener
 *
 * @param string $projectId  The Google project ID.
 * @param string $topicName  The Pub/Sub topic name.
 * @param string $subscriptionId  The ID of the subscription.
 */
function subscriber_error_listener(
    string $projectId,
    string $topicName,
    string $subscriptionId
): void {

    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);
    $subscription = $pubsub->subscription($subscriptionId, $topicName);

    try {
        $messages = $subscription->pull();
        foreach ($messages as $message) {
            printf('PubSub Message: %s' . PHP_EOL, $message->data());
            $subscription->acknowledge($message);
        }
    } catch (\Exception $e) { // Handle unrecoverable exceptions
        printf('Exception Message: %s' . PHP_EOL, $e->getMessage());
        printf('StackTrace: %s' . PHP_EOL, $e->getTraceAsString());
    }
}
# [END pubsub_subscriber_error_listener]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
