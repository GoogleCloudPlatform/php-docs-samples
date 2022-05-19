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

# [START pubsub_create_subscription_with_filter]
use Google\Cloud\PubSub\PubSubClient;

/**
 * Creates a Pub/Sub subscription.
 *
 * @param string $projectId  The Google project ID.
 * @param string $topicName  The Pub/Sub topic name.
 * @param string $subscriptionName  The Pub/Sub subscription name.
 * @param string $filter  The Pub/Sub subscription filter.
 */
function create_subscription_with_filter($projectId, $topicName, $subscriptionName, $filter)
{
    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);
    $topic = $pubsub->topic($topicName);
    $subscription = $topic->subscription($subscriptionName);

    $subscription->create(['filter' => $filter]);

    printf('Subscription created: %s' . PHP_EOL, $subscription->name());
    printf('Subscription info: %s' . PHP_EOL, json_encode($subscription->info()));
}
# [END pubsub_create_subscription_with_filter]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
