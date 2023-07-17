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

# [START pubsub_create_cloud_storage_subscription]
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\V1\CloudStorageConfig;

/**
 * Creates a Pub/Sub GCS subscription.
 *
 * @param string $projectId  The Google project ID.
 * @param string $topicName  The Pub/Sub topic name.
 * @param string $subscriptionName  The Pub/Sub subscription name.
 * @param string $tableName  The Cloud Storage bucket name.
 *        The bucket name must be without any prefix like "gs://".
 */
function create_storage_subscription($projectId, $topicName, $subscriptionName, $bucket)
{
    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);
    $topic = $pubsub->topic($topicName);
    $subscription = $topic->subscription($subscriptionName);
    $config = new CloudStorageConfig(['bucket' => $bucket]);
    $subscription->create([
        'cloudStorageConfig' => $config
    ]);

    printf('Subscription created: %s' . PHP_EOL, $subscription->name());
}
# [END pubsub_create_cloud_storage_subscription]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
