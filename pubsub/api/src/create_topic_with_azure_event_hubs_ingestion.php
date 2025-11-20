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

# [START pubsub_create_topic_with_azure_event_hubs_ingestion]
use Google\Cloud\PubSub\PubSubClient;

/**
 * Creates a Pub/Sub topic with Azure Event Hubs ingestion.
 *
 * @param string $projectId  The Google project ID.
 * @param string $topicName  The Pub/Sub topic name.
 * @param string $resourceGroup  The name of the resource group within the azure subscription.
 * @param string $namespace  The name of the Event Hubs namespace.
 * @param string $eventHub  The name of the Event Hub.
 * @param string $clientId  The client id of the Azure application that is being used to authenticate Pub/Sub.
 * @param string $tenantId  The tenant id of the Azure application that is being used to authenticate Pub/Sub.
 * @param string $subscriptionId  The Azure subscription id.
 * @param string $gcpServiceAccount  The GCP service account to be used for Federated Identity authentication.
 */
function create_topic_with_azure_event_hubs_ingestion(
    string $projectId,
    string $topicName,
    string $resourceGroup,
    string $namespace,
    string $eventHub,
    string $clientId,
    string $tenantId,
    string $subscriptionId,
    string $gcpServiceAccount
): void {
    $pubsub = new PubSubClient([
        'projectId' => $projectId,
    ]);

    $topic = $pubsub->createTopic($topicName, [
        'ingestionDataSourceSettings' => [
            'azure_event_hubs' => [
                'resource_group' => $resourceGroup,
                'namespace' => $namespace,
                'event_hub' => $eventHub,
                'client_id' => $clientId,
                'tenant_id' => $tenantId,
                'subscription_id' => $subscriptionId,
                'gcp_service_account' => $gcpServiceAccount
            ]
        ]
    ]);

    printf('Topic created: %s' . PHP_EOL, $topic->name());
}
# [END pubsub_create_topic_with_azure_event_hubs_ingestion]
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
