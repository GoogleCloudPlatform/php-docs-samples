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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/monitoring/README.md
 */

namespace Google\Cloud\Samples\Monitoring;

// [START monitoring_alert_enable_policies]
use Google\Cloud\Monitoring\V3\Client\AlertPolicyServiceClient;
use Google\Cloud\Monitoring\V3\ListAlertPoliciesRequest;
use Google\Cloud\Monitoring\V3\UpdateAlertPolicyRequest;
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
    $projectName = 'projects/' . $projectId;
    $listAlertPoliciesRequest = (new ListAlertPoliciesRequest())
        ->setName($projectName)
        ->setFilter($filter);

    $policies = $alertClient->listAlertPolicies($listAlertPoliciesRequest);
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
            $updateAlertPolicyRequest = (new UpdateAlertPolicyRequest())
                ->setAlertPolicy($policy)
                ->setUpdateMask($mask);
            $alertClient->updateAlertPolicy($updateAlertPolicyRequest);
            printf('%s %s' . PHP_EOL,
                $enable ? 'Enabled' : 'Disabled',
                $policy->getName()
            );
        }
    }
}
// [END monitoring_alert_enable_policies]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
