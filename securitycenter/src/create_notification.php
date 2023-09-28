<?php
/**
 * Copyright 2020 Google LLC.
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

namespace Google\Cloud\Samples\SecurityCenter;

// [START securitycenter_create_notification_config]
use Google\Cloud\SecurityCenter\V1\SecurityCenterClient;
use Google\Cloud\SecurityCenter\V1\NotificationConfig;
use Google\Cloud\SecurityCenter\V1\NotificationConfig\StreamingConfig;

/**
 * @param string $organizationId        Your org ID
 * @param string $notificationConfigId  A unique identifier
 * @param string $projectId             Your Cloud Project ID
 * @param string $topicName             Your topic name
 */
function create_notification(
    string $organizationId,
    string $notificationConfigId,
    string $projectId,
    string $topicName
): void {
    $securityCenterClient = new SecurityCenterClient();
    // 'parent' must be in one of the following formats:
    //		"organizations/{orgId}"
    //		"projects/{projectId}"
    //		"folders/{folderId}"
    $parent = $securityCenterClient::organizationName($organizationId);
    $pubsubTopic = $securityCenterClient::topicName($projectId, $topicName);

    $streamingConfig = (new StreamingConfig())->setFilter('state = "ACTIVE"');
    $notificationConfig = (new NotificationConfig())
        ->setDescription('A sample notification config')
        ->setPubsubTopic($pubsubTopic)
        ->setStreamingConfig($streamingConfig);

    $response = $securityCenterClient->createNotificationConfig(
        $parent,
        $notificationConfigId,
        $notificationConfig
    );
    printf('Notification config was created: %s' . PHP_EOL, $response->getName());
}
// [END securitycenter_create_notification_config]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
