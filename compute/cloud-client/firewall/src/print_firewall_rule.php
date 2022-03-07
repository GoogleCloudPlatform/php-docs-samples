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

use Google\Cloud\Compute\V1\FirewallsClient;

/**
 * Prints details about a particular firewall rule in the specified project.
 *
 * @param string $projectId Project ID or project number of the Cloud project you want to print a rule from.
 * @param string $firewallRuleName Unique name for the firewall rule.
 *
 * @throws \Google\ApiCore\ApiException if the remote call fails.
 */
function print_firewall_rule(string $projectId, string $firewallRuleName)
{
    // Get details of a firewall rule defined for the project using Firewalls Client.
    $firewallClient = new FirewallsClient();
    $response = $firewallClient->get($firewallRuleName, $projectId);
    $direction = $response->getDirection();
    printf('ID: %s' . PHP_EOL, $response->getID());
    printf('Kind: %s' . PHP_EOL, $response->getKind());
    printf('Name: %s' . PHP_EOL, $response->getName());
    printf('Creation Time: %s' . PHP_EOL, $response->getCreationTimestamp());
    printf('Direction: %s' . PHP_EOL, $direction);
    printf('Network: %s' . PHP_EOL, $response->getNetwork());
    printf('Disabled: %s' . PHP_EOL, var_export($response->getDisabled(), true));
    printf('Priority: %s' . PHP_EOL, $response->getPriority());
    printf('Self Link: %s' . PHP_EOL, $response->getSelfLink());
    printf('Logging Enabled: %s' . PHP_EOL, var_export($response->getLogConfig()->getEnable(), true));
    print('--Allowed--' . PHP_EOL);
    foreach ($response->getAllowed() as $item) {
        printf('Protocol: %s' . PHP_EOL, $item->getIPProtocol());
        foreach ($item->getPorts()as $ports) {
            printf(' - Ports: %s' . PHP_EOL, $ports);
        }
    }
    print('--Source Ranges--' . PHP_EOL);
    foreach ($response->getSourceRanges()as $ranges) {
        printf(' - Range: %s' . PHP_EOL, $ranges);
    }
}

require_once __DIR__ . '/../../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
