<?php
/**
 * Copyright 2020 Google LLC
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

# [START pubsub_dead_letter_delivery_attempt]
use Google\Cloud\PubSub\Message;
use Google\Cloud\PubSub\PubSubClient;

/**
 * Get the delivery attempt from a pulled message.
 *
 * @param string $projectId  The Google project ID.
 * @param string $topicName  The Pub/Sub topic name.
 * @param string $subscriptionName  The Pub/Sub subscription name.
 * @param string $message The contents of a pubsub message data field.
 */
function dead_letter_delivery_attempt($projectId, $topicName, $subscriptionName, $message)
{
    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);

    $topic = $pubsub->topic($topicName);

    // publish test message
    $topic->publish(new Message([
        'data' => $message
    ]));

    $subscription = $topic->subscription($subscriptionName);
    $messages = $subscription->pull();

    foreach ($messages as $message) {
        printf('Received message %s' . PHP_EOL, $message->data());
        printf('Delivery attempt %d' . PHP_EOL, $message->deliveryAttempt());
    }
    print('Done' . PHP_EOL);
}
# [END pubsub_dead_letter_delivery_attempt]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
