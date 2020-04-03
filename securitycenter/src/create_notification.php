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
    return printf('Usage: php %s ORGANIZATION_ID NOTIFICATION_ID PROJECT_ID TOPIC_NAME\n', basename(__FILE__));
}
list($_, $organizationId, $notificationConfigId, $projectId, $topicName) = $argv;

// [START scc_create_notification_config]
use Google\Cloud\SecurityCenter\V1\SecurityCenterClient;
use Google\Cloud\SecurityCenter\V1\NotificationConfig;
use Google\Cloud\SecurityCenter\V1\NotificationConfig\StreamingConfig;

/** Uncomment and populate these variables in your code */
// $organizationId = "{your-org-id}";
// $notificationConfigId = {"your-unique-id"};
// $projectId = "{your-project}"";
// $topicName = "{your-topic}";

$securityCenterClient = new SecurityCenterClient();
$organizationName = $securityCenterClient::organizationName($organizationId);
$pubsubTopic = $securityCenterClient::topicName($projectId, $topicName);

$streamingConfig = (new StreamingConfig())->setFilter("state = \"ACTIVE\"");
$notificationConfig = (new NotificationConfig())
    ->setDescription('A sample notification config')
    ->setPubsubTopic($pubsubTopic)
    ->setStreamingConfig($streamingConfig);

$response = $securityCenterClient->createNotificationConfig(
    $organizationName,
    $notificationConfigId,
    $notificationConfig
);
printf('Notification config was created: %s' . PHP_EOL, $response->getName());

// [END scc_create_notification_config]
