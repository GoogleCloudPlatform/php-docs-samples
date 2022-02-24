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

# [START compute_firewall_create]
use Google\Cloud\Compute\V1\FirewallsClient;
use Google\Cloud\Compute\V1\Allowed;
use Google\Cloud\Compute\V1\Firewall;

/**
 * To correctly handle string enums in Cloud Compute library
 * use constants defined in the Enums subfolder.
 */
use Google\Cloud\Compute\V1\Enums\Firewall\Direction;

/**
 * Creates a simple firewall rule allowing incoming HTTP and HTTPS access from the entire internet.
 *
 * @param string $projectId Project ID or project number of the Cloud project you want to create a rule for.
 * @param string $firewallRuleName Name of the rule that is created.
 * @param string $network Name of the network the rule will be applied to. Available name formats:
 *                        https://www.googleapis.com/compute/v1/projects/{project_id}/global/networks/{network}
 *                        projects/{project_id}/global/networks/{network}
 *                        global/networks/{network}
 *
 * @throws \Google\ApiCore\ApiException if the remote call fails.
 * @throws \Google\ApiCore\ValidationException if local error occurs before remote call.
 */

function create_firewall_rule(string $projectId, string $firewallRuleName, string $network = 'global/networks/default')
{
    $firewallsClient = new FirewallsClient();
    $allowedPorts = (new Allowed())
      ->setIPProtocol('tcp')
      ->setPorts(['80', '443']);
    $firewallResource = (new Firewall())
      ->setName($firewallRuleName)
      ->setDirection(Direction::INGRESS)
      ->setAllowed([$allowedPorts])
      ->setSourceRanges(['0.0.0.0/0'])
      ->setTargetTags(['web'])
      ->setNetwork($network)
      ->setDescription('Allowing TCP traffic on ports 80 and 443 from Internet.');

    /**
    * Note that the default value of priority for the firewall API is 1000.
    * If you check the value of its priority at this point it will be
    * equal to 0, however it is not treated as "set" by the library and thus
    * the default will be applied to the new rule. If you want to create a rule
    * that has priority == 0, you need to explicitly set it so:
    *
    *   $firewallResource->setPriority(0);
    */

    //Create the firewall rule using Firewalls Client.
    $operation = $firewallsClient->insert($firewallResource, $projectId);

    // Wait for the operation to complete.
    $operation->pollUntilComplete();
    if ($operation->operationSucceeded()) {
        printf('Created rule %s.' . PHP_EOL, $firewallRuleName);
    } else {
        $error = $operation->getError();
        printf('Firewall rule creation failed: %s' . PHP_EOL, $error->getMessage());
    }
}
# [END compute_firewall_create]

require_once __DIR__ . '/../../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
