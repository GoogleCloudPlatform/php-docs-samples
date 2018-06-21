<?php
/**
 * Copyright 2018 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/monitoring/README.md
 */

namespace Google\Cloud\Samples\Monitoring;

// [START monitoring_alert_backup_policies]
use Google\Cloud\Monitoring\V3\AlertPolicyServiceClient;
use Google\Cloud\Monitoring\V3\NotificationChannelServiceClient;

/**
 * Back up alert policies.
 *
 * @param string $projectId Your project ID
 */
function alert_backup_policies($projectId)
{
    $alertClient = new AlertPolicyServiceClient([
        'projectId' => $projectId,
    ]);
    $channelClient = new NotificationChannelServiceClient([
        'projectId' => $projectId,
    ]);
    $projectName = $alertClient->projectName($projectId);

    $record = [
        'project_name' => $projectName,
        'policies' => [],
        'channels' => [],
    ];
    $policies = $alertClient->listAlertPolicies($projectName);
    foreach ($policies->iterateAllElements() as $policy) {
        $record['policies'][] = json_decode($policy->serializeToJsonString());
    }
    $channels = $channelClient->listNotificationChannels($projectName);
    foreach ($channels->iterateAllElements() as $channel) {
        $record['channels'][] = json_decode($channel->serializeToJsonString());
    }
    file_put_contents('backup.json', json_encode($record, JSON_PRETTY_PRINT));
    print('Backed up alert policies and notification channels to backup.json.');
}
// [END monitoring_alert_backup_policies]
