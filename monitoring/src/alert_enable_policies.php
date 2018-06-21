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

// [START monitoring_alert_enable_policies]
use Google\Cloud\Monitoring\V3\AlertPolicyServiceClient;
use Google\Protobuf\FieldMask;

/**
 * Enable or disable alert policies in a project.
 *
 * @param string $projectId Your project ID
 * @param bool $enable Enable or disable the policies.
 * @param string $filter Only enable/disable alert policies that match a filter.
 *        See https://cloud.google.com/monitoring/api/v3/sorting-and-filtering
 */
function alert_enable_policies($projectId, $enable = true, $filter = null)
{
    $alertClient = new AlertPolicyServiceClient([
        'projectId' => $projectId,
    ]);
    $projectName = $alertClient->projectName($projectId);

    $policies = $alertClient->listAlertPolicies($projectName, [
        'filter' => $filter
    ]);
    foreach ($policies->iterateAllElements() as $policy) {
        $isEnabled = $policy->getEnabled()->getValue();
        if ($enable == $isEnabled) {
            printf('Policy %s is already %s' . PHP_EOL,
                $policy->getName(),
                $isEnabled ? 'enabled' : 'disabled'
            );
        } else {
            $policy->getEnabled()->setValue((bool) $enable);
            $mask = new FieldMask();
            $mask->setPaths(['enabled']);
            $alertClient->updateAlertPolicy($policy, [
                'updateMask' => $mask
            ]);
            printf('%s %s' . PHP_EOL,
                $enable ? 'Enabled' : 'Disabled',
                $policy->getName()
            );
        }
    }
}
// [END monitoring_alert_enable_policies]
