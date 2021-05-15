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

# [START pubsub_dead_letter_create_subscription]
use Google\Cloud\PubSub\PubSubClient;

/**
 * Creates a Pub/Sub subscription with dead letter policy enabled.
 *
 * @param string $projectId  The Google project ID.
 * @param string $topicName  The Pub/Sub topic name.
 * @param string $subscriptionName  The Pub/Sub subscription name.
 * @param string $deadLetterTopicName The Pub/Sub topic to use for dead letter policy.
 */
function dead_letter_create_subscription($projectId, $topicName, $subscriptionName, $deadLetterTopicName)
{
    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);

    $topic = $pubsub->topic($topicName);
    $deadLetterTopic = $pubsub->topic($deadLetterTopicName);

    $subscription = $topic->subscribe($subscriptionName, [
        'deadLetterPolicy' => [
            'deadLetterTopic' => $deadLetterTopic
        ]
    ]);

    printf(
        'Subscription %s created with dead letter topic %s' . PHP_EOL,
        $subscription->name(),
        $deadLetterTopic->name()
    );
}
# [END pubsub_dead_letter_create_subscription]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
