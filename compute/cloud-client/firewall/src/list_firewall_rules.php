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

# [START compute_firewall_list]
use Google\Cloud\Compute\V1\FirewallsClient;

/**
 * Return a list of all the firewall rules in specified project. Also prints the
 * list of firewall names and their descriptions.
 *
 * @param string $projectId Project ID or project number of the Cloud project you want to list rules from.
 *
 * @throws \Google\ApiCore\ApiException if the remote call fails.
 */
function list_firewall_rules(string $projectId)
{
    // List all firewall rules defined for the project using Firewalls Client.
    $firewallClient = new FirewallsClient();
    $firewallList = $firewallClient->list($projectId);

    print('--- Firewall Rules ---' . PHP_EOL);
    foreach ($firewallList->iterateAllElements() as $firewall) {
        printf(' -  %s : %s : %s' . PHP_EOL, $firewall->getName(), $firewall->getDescription(), $firewall->getNetwork());
    }
}
# [END compute_firewall_list]

require_once __DIR__ . '/../../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
