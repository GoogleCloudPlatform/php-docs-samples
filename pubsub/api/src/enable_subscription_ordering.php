<?php
/**
 * Copyright 2023 Google Inc.
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

# [START pubsub_enable_subscription_ordering]
use Google\Cloud\PubSub\PubSubClient;

/**
 * Creates a Pub/Sub subscription.
 *
 * @param string $projectId  The Google project ID.
 * @param string $topicName  The Pub/Sub topic name.
 * @param string $subscriptionName  The Pub/Sub subscription name.
 */
function enable_subscription_ordering($projectId, $topicName, $subscriptionName)
{
    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);
    $topic = $pubsub->topic($topicName);
    $subscription = $topic->subscription($subscriptionName);

    $subscription->create(['enableMessageOrdering' => true]);

    printf('Created subscription with ordering: %s' . PHP_EOL, $subscription->name());
    printf('Subscription info: %s' . PHP_EOL, json_encode($subscription->info()));
}
# [END pubsub_enable_subscription_ordering]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
