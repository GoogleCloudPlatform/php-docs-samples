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

// [START monitoring_alert_replace_channels]
use Google\Cloud\Monitoring\V3\AlertPolicyServiceClient;
use Google\Cloud\Monitoring\V3\NotificationChannelServiceClient;
use Google\Cloud\Monitoring\V3\AlertPolicy;
use Google\Protobuf\FieldMask;

/**
 * @param string $projectId Your project ID
 * @param string $alertPolicyId Your alert policy id ID
 * @param array $channelIds array of channel IDs
 */
function alert_replace_channels($projectId, $alertPolicyId, array $channelIds)
{
    $alertClient = new AlertPolicyServiceClient([
        'projectId' => $projectId,
    ]);

    $channelClient = new NotificationChannelServiceClient([
        'projectId' => $projectId,
    ]);
    $policy = new AlertPolicy();
    $policy->setName($alertClient->alertPolicyName($projectId, $alertPolicyId));

    $newChannels = [];
    foreach ($channelIds as $channelId) {
        $newChannels[] = $channelClient->notificationChannelName($projectId, $channelId);
    }
    $policy->setNotificationChannels($newChannels);
    $mask = new FieldMask();
    $mask->setPaths(['notification_channels']);
    $updatedPolicy = $alertClient->updateAlertPolicy($policy, [
        'updateMask' => $mask,
    ]);
    printf('Updated %s' . PHP_EOL, $updatedPolicy->getName());
}
// [END monitoring_alert_replace_channels]
