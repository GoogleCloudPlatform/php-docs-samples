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

# [START monitoring_alert_delete_channel]
use Google\Cloud\Monitoring\V3\NotificationChannelServiceClient;

/**
 * @param string $projectId Your project ID
 */
function alert_delete_channel($projectId, $channelId)
{
    $channelClient = new NotificationChannelServiceClient([
        'projectId' => $projectId,
    ]);
    $channelName = $channelClient->notificationChannelName($projectId, $channelId);

    $channelClient->deleteNotificationChannel($channelName);
    printf('Deleted notification channel %s' . PHP_EOL, $channelName);
}
# [END monitoring_alert_delete_channel]
