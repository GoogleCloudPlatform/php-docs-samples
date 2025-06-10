<?php
/**
 * Copyright 2022 Google LLC
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

# [START pubsub_subscriber_exactly_once]
use Google\Cloud\PubSub\PubSubClient;

/**
 * Subscribe and pull messages from a subscription
 * with `Exactly Once Delivery` enabled.
 *
 * @param string $projectId
 * @param string $subscriptionId
 */
function subscribe_exactly_once_delivery(
    string $projectId,
    string $subscriptionId
): void {
    $pubsub = new PubSubClient([
        'projectId' => $projectId,
        // use the apiEndpoint option to set a regional endpoint
        'apiEndpoint' => 'us-west1-pubsub.googleapis.com:443'
    ]);

    $subscription = $pubsub->subscription($subscriptionId);
    $messages = $subscription->pull();

    foreach ($messages as $message) {
        // When exactly once delivery is enabled on the subscription,
        // the message is guaranteed to not be delivered again if the ack succeeds.
        // Passing the `returnFailures` flag retries any temporary failures received
        // while acking the msg and also returns any permanently failed msgs.
        // Passing this flag on a subscription with exactly once delivery disabled
        // will always return an empty array.
        $failedMsgs = $subscription->acknowledge($message, ['returnFailures' => true]);

        if (empty($failedMsgs)) {
            printf('Acknowledged message: %s' . PHP_EOL, $message->data());
        } else {
            // Either log or store the $failedMsgs to be retried later
        }
    }
}
# [END pubsub_subscriber_exactly_once]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
