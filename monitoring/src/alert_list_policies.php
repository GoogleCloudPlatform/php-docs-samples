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

// [START monitoring_alert_list_policies]
use Google\Cloud\Monitoring\V3\AlertPolicyServiceClient;

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

    $policies = $alertClient->listAlertPolicies(
        $alertClient->projectName($projectId)
    );
    foreach ($policies->iterateAllElements() as $policy) {
        printf('Name: %s (%s)' . PHP_EOL, $policy->getDisplayName(), $policy->getName());
    }
}
// [END monitoring_alert_list_policies]
