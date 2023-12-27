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

// [START monitoring_alert_list_policies]
use Google\Cloud\Monitoring\V3\Client\AlertPolicyServiceClient;
use Google\Cloud\Monitoring\V3\ListAlertPoliciesRequest;

/**
 * Adds a new column to the Albums table in the example database.
 * Example:
 * ```
 * alert_list_policies($projectId);
 * ```
 *
 * @param string $projectId Your project ID
 */
function alert_list_policies($projectId)
{
    $alertClient = new AlertPolicyServiceClient([
        'projectId' => $projectId,
    ]);
    $listAlertPoliciesRequest = (new ListAlertPoliciesRequest())
        ->setName($alertClient->projectName($projectId));

    $policies = $alertClient->listAlertPolicies($listAlertPoliciesRequest);
    foreach ($policies->iterateAllElements() as $policy) {
        printf('Name: %s (%s)' . PHP_EOL, $policy->getDisplayName(), $policy->getName());
    }
}
// [END monitoring_alert_list_policies]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
