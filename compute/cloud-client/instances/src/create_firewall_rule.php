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

# [START compute_firewall_create]
use Google\Cloud\Compute\V1\FirewallsClient;
use Google\Cloud\Compute\V1\Allowed;
use Google\Cloud\Compute\V1\Firewall;

/**
 * Creates a simple firewall rule allowing for incoming HTTP and HTTPS access from the entire Internet.
 *
 * Example:
 * ```
 * create_firewall_rule($projectId, $firewallRule, $network);
 * ```
 *
 * @param string $projectId Project ID or project number of the Cloud project you want to create a rule for.
 * @param string $firewallRule Name of the rule that is created.
 * @param string $network Name of the network the rule will be applied to. Available name formats:
 *                        https://www.googleapis.com/compute/v1/projects/{project_id}/global/networks/{network}
 *                        projects/{project_id}/global/networks/{network}
 *                        global/networks/{network}
 *
 * @throws \Google\ApiCore\ApiException if the remote call fails.
 */
function create_firewall_rule(string $projectId, string $firewallRule, string $network = 'global/networks/default')
{
  $firewallsClient = new FirewallsClient();
  $tcp_80_443_allowed = (new Allowed())
      ->setIPProtocol('tcp')
      ->setPorts(['80', '443']);
  $firewallResource = (new Firewall())
      ->setName($firewallRule)
      ->setDirection(0)
      ->setAllowed([$tcp_80_443_allowed])
      ->setSourceRanges(['0.0.0.0/0'])
      ->setNetwork($network)
      ->setDescription('Allowing TCP traffic on port 80 and 443 from Internet.');

  /**
  * Note that the default value of priority for the firewall API is 1000.
  * If you check the value of its priority at this point it will be
  * equal to 0, however it is not treated as "set" by the library and thus
  * the default will be applied to the new rule. If you want to create a rule
  * that has priority == 0, you need to explicitly set it so:
  *
  *   ->setPriority(0)
  */

  //Create the firewall rule using Firewalls Client.
  $operation = $firewallsClient->insert($firewallResource, $projectId);

  // Wait for the create operation to complete using a custom helper function.
  // @see src/wait_for_operation.php
  $operation = wait_for_operation($operation, $projectId);
  if (empty($operation->getError())) {
        printf('Created rule %s' . PHP_EOL, $firewallRule);
  } else {
        printf('Firewall rule creation failed!' . PHP_EOL);
  }
}
# [END compute_firewall_create]

require_once __DIR__ . '/../../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
