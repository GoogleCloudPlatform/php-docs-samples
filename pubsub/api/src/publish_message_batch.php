<?php
/**
 * Copyright 2019 Google LLC
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

# [START pubsub_publisher_batch_settings]
use Google\Cloud\PubSub\PubSubClient;

/**
 * Publishes a message for a Pub/Sub topic.
 *
 * The publisher should be used in conjunction with the `google-cloud-batch`
 * daemon, which should be running in the background.
 *
 * To start the daemon, from your project root call `vendor/bin/google-cloud-batch daemon`.
 *
 * @param string $projectId  The Google project ID.
 * @param string $topicName  The Pub/Sub topic name.
 * @param string $message    The message to publish.
 */
function publish_message_batch($projectId, $topicName, $message)
{
    // Check if the batch daemon is running.
    if (getenv('IS_BATCH_DAEMON_RUNNING') !== 'true') {
        trigger_error(
            'The batch daemon is not running. Call ' .
            '`vendor/bin/google-cloud-batch daemon` from ' .
            'your project root to start the daemon.',
            E_USER_NOTICE
        );
    }

    $batchOptions = [
        'batchSize' => 100, // Max messages for each batch.
        'callPeriod' => 0.01, // Max time in seconds between each batch publish.
    ];

    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);
    $topic = $pubsub->topic($topicName);
    $publisher = $topic->batchPublisher([
        'batchOptions' => $batchOptions
    ]);

    for ($i = 0; $i < 10; $i++) {
        $publisher->publish(['data' => $message]);
    }

    print('Messages enqueued for publication.' . PHP_EOL);
}
# [END pubsub_publisher_batch_settings]
