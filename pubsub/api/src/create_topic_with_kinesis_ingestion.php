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

# [START pubsub_create_topic_with_kinesis_ingestion]
use Google\Cloud\PubSub\PubSubClient;

/**
 * Creates a Pub/Sub topic with AWS Kinesis ingestion.
 *
 * @param string $projectId  The Google project ID.
 * @param string $topicName  The Pub/Sub topic name.
 * @param string $streamArn  The Kinesis stream ARN to ingest data from.
 * @param string $consumerArn  The Kinesis consumer ARN to used for ingestion in Enhanced Fan-Out mode. The consumer must be already created and ready to be used.
 * @param string $awsRoleArn  AWS role ARN to be used for Federated Identity authentication with Kinesis. Check the Pub/Sub docs for how to set up this role and the required permissions that need to be attached to it.
 * @param string $gcpServiceAccount  The GCP service account to be used for Federated Identity authentication with Kinesis (via a AssumeRoleWithWebIdentity call for the provided role). The $awsRoleArn must be set up with accounts.google.com:sub equals to this service account number.
 */
function create_topic_with_kinesis_ingestion(
    string $projectId,
    string $topicName,
    string $streamArn,
    string $consumerArn,
    string $awsRoleArn,
    string $gcpServiceAccount
): void {
    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);

    $topic = $pubsub->createTopic($topicName, [
        'ingestionDataSourceSettings' => [
            'aws_kinesis' => [
                'stream_arn' => $streamArn,
                'consumer_arn' => $consumerArn,
                'aws_role_arn' => $awsRoleArn,
                'gcp_service_account' => $gcpServiceAccount
            ]
        ]
    ]);

    printf('Topic created: %s' . PHP_EOL, $topic->name());
}
# [END pubsub_create_topic_with_kinesis_ingestion]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
