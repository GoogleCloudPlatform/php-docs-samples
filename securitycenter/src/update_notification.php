<?php
/**
 * Copyright 2020 Google LLC.
 *
 * Licensed under the Apache License, Version 2.0 (the 'License');
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an 'AS IS' BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Samples\SecurityCenter;

// [START securitycenter_update_notification_config]
use Google\Cloud\SecurityCenter\V1\Client\SecurityCenterClient;
use Google\Cloud\SecurityCenter\V1\NotificationConfig;
use Google\Cloud\SecurityCenter\V1\NotificationConfig\StreamingConfig;
use Google\Cloud\SecurityCenter\V1\UpdateNotificationConfigRequest;
use Google\Protobuf\FieldMask;

/**
 * @param string $organizationId        Your org ID
 * @param string $notificationConfigId  A unique identifier
 * @param string $projectId             Your Cloud Project ID
 * @param string $topicName             Your topic name
 */
function update_notification(
    string $organizationId,
    string $notificationConfigId,
    string $projectId,
    string $topicName
): void {
    $securityCenterClient = new SecurityCenterClient();

    // Ensure this ServiceAccount has the 'pubsub.topics.setIamPolicy' permission on the topic.
    // https://cloud.google.com/pubsub/docs/reference/rest/v1/projects.topics/setIamPolicy
    $pubsubTopic = $securityCenterClient::topicName($projectId, $topicName);
    // You can also use 'projectId' or 'folderId' instead of the 'organizationId'.
    $notificationConfigName = $securityCenterClient::notificationConfigName($organizationId, $notificationConfigId);

    $streamingConfig = (new StreamingConfig())->setFilter('state = "ACTIVE"');
    $fieldMask = (new FieldMask())->setPaths(['description', 'pubsub_topic', 'streaming_config.filter']);
    $notificationConfig = (new NotificationConfig())
        ->setName($notificationConfigName)
        ->setDescription('Updated description.')
        ->setPubsubTopic($pubsubTopic)
        ->setStreamingConfig($streamingConfig);
    $updateNotificationConfigRequest = (new UpdateNotificationConfigRequest())
        ->setNotificationConfig($notificationConfig);

    $response = $securityCenterClient->updateNotificationConfig($updateNotificationConfigRequest);
    printf('Notification config was updated: %s' . PHP_EOL, $response->getName());
}
// [END securitycenter_update_notification_config]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
