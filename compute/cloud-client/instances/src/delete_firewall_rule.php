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

# [START compute_firewall_delete]
use Google\Cloud\Compute\V1\FirewallsClient;

/**
 * Delete a firewall rule from the specified project.
 *
 * Example:
 * ```
 * delete_firewall_rule($projectId, $firewallRule);
 * ```
 *
 * @param string $projectId Project ID or project number of the Cloud project you want to delete a rule for.
 * @param string $firewallRule Name of the rule that is deleted.
 *
 * @throws \Google\ApiCore\ApiException if the remote call fails.
 */
function delete_firewall_rule(string $projectId, string $firewallRule)
{
  $firewallsClient = new FirewallsClient();

  // Delete the firewall rule using Firewalls Client.
  $operation = $firewallsClient->delete($firewallRule, $projectId);

  // Wait for the create operation to complete using a custom helper function.
  // @see src/wait_for_operation.php
  $operation = wait_for_operation($operation, $projectId);
  if (empty($operation->getError())) {
      printf('Rule Deleted! %s' . PHP_EOL, $firewallRule);
  } else {
      print('Deletion failed!' . PHP_EOL);
  }
}
# [END compute_firewall_delete]

require_once __DIR__ . '/../../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
