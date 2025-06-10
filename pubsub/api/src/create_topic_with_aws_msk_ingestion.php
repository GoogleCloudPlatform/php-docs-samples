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

# [START pubsub_create_topic_with_aws_msk_ingestion]
use Google\Cloud\PubSub\PubSubClient;

/**
 * Creates a Pub/Sub topic with AWS MSK ingestion.
 *
 * @param string $projectId  The Google project ID.
 * @param string $topicName  The Pub/Sub topic name.
 * @param string $clusterArn  The Amazon Resource Name (ARN) that uniquely identifies the cluster.
 * @param string $mskTopic  The name of the topic in the Amazon MSK cluster that Pub/Sub will import from.
 * @param string $awsRoleArn  AWS role ARN to be used for Federated Identity authentication with Amazon MSK.
 *               Check the Pub/Sub docs for how to set up this role and the required permissions that need to be
 *               attached to it.
 * @param string $gcpServiceAccount  The GCP service account to be used for Federated Identity authentication
 *               with Amazon MSK (via a AssumeRoleWithWebIdentity call for the provided role). The aws_role_arn
 *               must be set up with accounts.google.com:sub equals to this service account number.
 */
function create_topic_with_aws_msk_ingestion(
    string $projectId,
    string $topicName,
    string $clusterArn,
    string $mskTopic,
    string $awsRoleArn,
    string $gcpServiceAccount
): void {
    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);

    $topic = $pubsub->createTopic($topicName, [
        'ingestionDataSourceSettings' => [
            'aws_msk' => [
                'cluster_arn' => $clusterArn,
                'topic' => $mskTopic,
                'aws_role_arn' => $awsRoleArn,
                'gcp_service_account' => $gcpServiceAccount
            ]
        ]
    ]);

    printf('Topic created: %s' . PHP_EOL, $topic->name());
}
# [END pubsub_create_topic_with_aws_msk_ingestion]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
