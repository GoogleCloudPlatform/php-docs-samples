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

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';
if (count($argv) < 4) {
    return printf("Usage: php %s ORGANIZATION_ID NOTIFICATION_ID PROJECT_ID TOPIC_NAME\n",  basename(__FILE__));
}
list($_, $organizationId, $notificationConfigId, $projectId, $topicName) = $argv;

// [START scc_update_notification_config]
use Google\Cloud\SecurityCenter\V1\SecurityCenterClient;
use Google\Cloud\SecurityCenter\V1\NotificationConfig;
use Google\Protobuf\FieldMask;

/** Uncomment and populate these variables in your code */
// $organizationId = "{your-org-id}";
// $notificationConfigId = {"your-unique-id"};
// $projectId = "{your-project}"";
// $topicName = "{your-topic}";

$securityCenterClient = new SecurityCenterClient();
$organizationName = "organizations/" . $organizationId;

// Ensure this ServiceAccount has the "pubsub.topics.setIamPolicy" permission on the topic.
$pubsubTopic = "projects/" . $projectId . "/topics/" . $topicName;
$notificationConfigName = $organizationName . "/notificationConfigs/" . $notificationConfigId;

try {
    $streamingConfig = new NotificationConfig\StreamingConfig();
    $streamingConfig->setFilter("state = \"ACTIVE\"");
    $notificationConfig = new NotificationConfig();
    $notificationConfig->setName($notificationConfigName);
    $notificationConfig->setDescription("Updated description.");
    $notificationConfig->setPubsubTopic($pubsubTopic);
    $fieldMask = new FieldMask();
    $fieldMask->setPaths(array("description", "pubsub_topic"));

    $response = $securityCenterClient->updateNotificationConfig($notificationConfig, array($fieldMask));
    printf("Notification config was updated: %s", $response->getName());

} finally {
    $securityCenterClient->close();
}

// [END scc_update_notification_config]