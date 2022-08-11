<?php
/**
 * Copyright 2022 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/blob/master/pubsub/api/README.md
 */

namespace Google\Cloud\Samples\PubSub;

# [START pubsub_create_subscription_with_exactly_once_delivery]
use Google\Cloud\PubSub\PubSubClient;

/**
 * Creates a Pub/Sub subscription with `Exactly Once Delivery` enabled.
 *
 * @param string $projectId  The Google project ID.
 * @param string $topicName  The Pub/Sub topic name.
 * @param string $subscriptionName  The Pub/Sub subscription name.
 */
function create_subscription_with_exactly_once_delivery(
    string $projectId,
    string $topicName,
    string $subscriptionName
): void {
    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);
    $topic = $pubsub->topic($topicName);
    $subscription = $topic->subscription($subscriptionName);
    $subscription->create([
        'enableExactlyOnceDelivery' => true
    ]);

    // Exactly Once Delivery status for the subscription
    $status = $subscription->info()['enableExactlyOnceDelivery'];

    printf('Subscription created with exactly once delivery status: %s' . PHP_EOL, $status ? 'true' : 'false');
}
# [END pubsub_create_subscription_with_exactly_once_delivery]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
