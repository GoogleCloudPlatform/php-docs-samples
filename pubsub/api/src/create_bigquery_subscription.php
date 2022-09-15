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

# [START pubsub_create_bigquery_subscription]
use Google\Cloud\PubSub\PubSubClient;
use Google\Cloud\PubSub\V1\BigQueryConfig;

/**
 * Creates a Pub/Sub BigQuery subscription.
 *
 * @param string $projectId  The Google project ID.
 * @param string $topicName  The Pub/Sub topic name.
 * @param string $subscriptionName  The Pub/Sub subscription name.
 * @param string $tableName  The BigQuery table to which to write.
 */
function create_bigquery_subscription($projectId, $topicName, $subscriptionName, $table)
{
    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);
    $topic = $pubsub->topic($topicName);
    $subscription = $topic->subscription($subscriptionName);
    $config = new BigQueryConfig(['table' => $table]);
    $subscription->create([
        'bigqueryConfig' => $config
    ]);

    printf('Subscription created: %s' . PHP_EOL, $subscription->name());
}
# [END pubsub_create_bigquery_subscription]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
