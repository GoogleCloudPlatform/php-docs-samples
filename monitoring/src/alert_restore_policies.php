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

# [START monitoring_alert_restore_policies]
# [START monitoring_alert_update_channel]
# [START monitoring_alert_enable_channel]
use Google\Cloud\Monitoring\V3\AlertPolicyServiceClient;
use Google\Cloud\Monitoring\V3\NotificationChannelServiceClient;
use Google\Cloud\Monitoring\V3\AlertPolicy;
use Google\Cloud\Monitoring\V3\NotificationChannel;
use Google\Cloud\Monitoring\V3\NotificationChannel\VerificationStatus;
use Google\ApiCore\ApiException;

/**
 * @param string $projectId Your project ID
 */
function alert_restore_policies($projectId)
{
    $alertClient = new AlertPolicyServiceClient([
        'projectId' => $projectId,
    ]);

    $channelClient = new NotificationChannelServiceClient([
        'projectId' => $projectId,
    ]);

    print('Loading alert policies and notification channels from backup.json.' . PHP_EOL);
    $projectName = $alertClient->projectName($projectId);
    $record = json_decode(file_get_contents('backup.json'), true);
    $isSameProject = $projectName == $record['project_name'];

    # Convert dicts to AlertPolicies.
    $policies = [];
    foreach ($record['policies'] as $policyArray) {
        $policy = new AlertPolicy();
        $policy->mergeFromJsonString(json_encode($policyArray));
        $policies[] = $policy;
    }

    # Convert dicts to NotificationChannels
    $channels = [];
    foreach (array_filter($record['channels']) as $channelArray) {
        $channel = new NotificationChannel();
        $channel->mergeFromJsonString(json_encode($channelArray));
        $channels[] = $channel;
    }

    # Restore the channels.
    $channelNameMap = [];
    foreach ($channels as $channel) {
        $updated = false;
        printf('Updating channel %s' . PHP_EOL, $channel->getDisplayName());

        # This field is immutable and it is illegal to specify a
        # non-default value (UNVERIFIED or VERIFIED) in the
        # Create() or Update() operations.
        $channel->setVerificationStatus(
            VerificationStatus::VERIFICATION_STATUS_UNSPECIFIED
        );

        if ($isSameProject) {
            try {
                $channelClient->updateNotificationChannel($channel);
                $updated = true;
            } catch (ApiException $e) {
                # The channel was deleted.  Create it below.
                if ($e->getStatus() !== 'NOT_FOUND') {
                    throw $e;
                }
            }
        }

        if (!$updated) {
            # The channel no longer exists.  Recreate it.
            $oldName = $channel->getName();
            $channel->setName('');
            $newChannel = $channelClient->createNotificationChannel(
                $projectName,
                $channel
            );
            $channelNameMap[$oldName] = $newChannel->getName();
        }
    }

    # Restore the alerts
    foreach ($policies as $policy) {
        printf('Updating policy %s' . PHP_EOL, $policy->getDisplayName());
        # These two fields cannot be set directly, so clear them.
        $policy->setCreationRecord(null);
        $policy->setMutationRecord(null);

        $notificationChannels = $policy->getNotificationChannels();

        # Update old channel names with new channel names.
        foreach ($notificationChannels as $i => $channel) {
            if (isset($channelNameMap[$channel])) {
                $notificationChannels[$i] = $channelNameMap[$channel];
            }
        }

        $updated = false;
        if ($isSameProject) {
            try {
                $alertClient->updateAlertPolicy($policy);
                $updated = true;
            } catch (ApiException $e) {
                # The policy was deleted.  Create it below.
                if ($e->getStatus() !== 'NOT_FOUND') {
                    throw $e;
                }
            }
        }

        if (!$updated) {
            # The policy no longer exists.  Recreate it.
            $oldName = $policy->getName();
            $policy->setName('');
            foreach ($policy->getConditions() as $condition) {
                $condition->setName('');
            }
            $policy = $alertClient->createAlertPolicy($projectName, $policy);
        }
        printf('Updated %s' . PHP_EOL, $policy->getName());
    }
    print('Restored alert policies and notification channels from backup.json.');
}
# [END monitoring_alert_enable_channel]
# [END monitoring_alert_restore_policies]
# [END monitoring_alert_update_channel]
