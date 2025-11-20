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

# [START pubsub_create_topic_with_confluent_cloud_ingestion]
use Google\Cloud\PubSub\PubSubClient;

/**
 * Creates a Pub/Sub topic with Confluent Cloud ingestion.
 *
 * @param string $projectId  The Google project ID.
 * @param string $topicName  The Pub/Sub topic name.
 * @param string $bootstrapServer  The address of the bootstrap server. The format is url:port.
 * @param string $clusterId  The id of the cluster.
 * @param string $confluentTopic  The name of the topic in the Confluent Cloud cluster that Pub/Sub will import from.
 * @param string $identityPoolId  The id of the identity pool to be used for Federated Identity authentication with Confluent Cloud.
 * @param string $gcpServiceAccount  The GCP service account to be used for Federated Identity authentication with identity_pool_id.
 */
function create_topic_with_confluent_cloud_ingestion(
    string $projectId,
    string $topicName,
    string $bootstrapServer,
    string $clusterId,
    string $confluentTopic,
    string $identityPoolId,
    string $gcpServiceAccount
): void {
    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);

    $topic = $pubsub->createTopic($topicName, [
        'ingestionDataSourceSettings' => [
            'confluent_cloud' => [
                'bootstrap_server' => $bootstrapServer,
                'cluster_id' => $clusterId,
                'topic' => $confluentTopic,
                'identity_pool_id' => $identityPoolId,
                'gcp_service_account' => $gcpServiceAccount
            ]
        ]
    ]);

    printf('Topic created: %s' . PHP_EOL, $topic->name());
}
# [END pubsub_create_topic_with_confluent_cloud_ingestion]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
