<?php
/**
 * Copyright 2022 Google Inc.
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

# [START compute_suspend_instance]
use Google\Cloud\Compute\V1\InstancesClient;

/**
 * Suspend a running Google Compute Engine instance.
 *
 * @param string $projectId Project ID or project number of the Cloud project your instance belongs to.
 * @param string $zone Name of the zone your instance belongs to.
 * @param string $instanceName Name of the instance you want to suspend.
  *
 * @throws \Google\ApiCore\ApiException if the remote call fails.
 * @throws \Google\ApiCore\ValidationException if local error occurs before remote call.
 */
function suspend_instance(
    string $projectId,
    string $zone,
    string $instanceName
) {
    // Suspend the running Compute Engine instance using InstancesClient.
    $instancesClient = new InstancesClient();
    $operation = $instancesClient->suspend($instanceName, $projectId, $zone);

    // Wait for the operation to complete.
    $operation->pollUntilComplete();
    if ($operation->operationSucceeded()) {
        printf('Instance %s suspended successfully' . PHP_EOL, $instanceName);
    } else {
        $error = $operation->getError();
        printf('Failed to suspend instance: %s' . PHP_EOL, $error->getMessage());
    }
}

# [END compute_suspend_instance]

require_once __DIR__ . '/../../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
