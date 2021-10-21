<?php
/**
 * Copyright 2021 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/compute/cloud-client/README.md
 */

namespace Google\Cloud\Samples\Compute;

include_once 'wait_for_operation.php';

# [START compute_firewall_patch]
use Google\Cloud\Compute\V1\FirewallsClient;
use Google\Cloud\Compute\V1\Firewall;

/**
 * Modifies the priority of a given firewall rule.
 *
 * Example:
 * ```
 * patch_firewall_priority($projectId, $firewallRuleName, $priority);
 * ```
 *
 * @param string $projectId Project ID or project number of the Cloud project you want to patch a rule from.
 * @param string $firewallRuleName Name of the rule that you want to modify.
 * @param int $priority The new priority to be set for the rule.
 *
 * @throws \Google\ApiCore\ApiException if the remote call fails.
 */
function patch_firewall_priority(string $projectId, string $firewallRuleName, int $priority)
{
    $firewallsClient = new FirewallsClient();
    $firewallResource = (new Firewall())->setPriority($priority);

    // The patch operation doesn't require the full definition of a Firewall object. It will only update
    // the values that were set in it, in this case it will only change the priority.
    $operation = $firewallsClient->patch($firewallRuleName, $firewallResource, $projectId);

    // Wait for the create operation to complete using a custom helper function.
    // @see src/wait_for_operation.php
    $operation = wait_for_operation($operation, $projectId);
    if (empty($operation->getError())) {
        printf('Patched %s priority to %d.' . PHP_EOL, $firewallRuleName, $priority);
    } else {
        print('Patching failed!' . PHP_EOL);
    }
}
# [END compute_firewall_patch]

require_once __DIR__ . '/../../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
