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

# [START compute_instances_delete]
use Google\Cloud\Compute\V1\InstancesClient;

/**
 * Delete an instance.
 *
 * @param string $projectId Your Google Cloud project ID.
 * @param string $zone Zone where the instance you want to delete is (like "us-central1-a").
 * @param string $instanceName Unique name for the Compute instance to delete.
 *
 * @throws \Google\ApiCore\ApiException if the remote call fails.
 * @throws \Google\ApiCore\ValidationException if local error occurs before remote call.
 */
function delete_instance(
    string $projectId,
    string $zone,
    string $instanceName
) {
    // Delete the Compute Engine instance using InstancesClient.
    $instancesClient = new InstancesClient();
    $operation = $instancesClient->delete($instanceName, $projectId, $zone);

    // Wait for the operation to complete.
    $operation->pollUntilComplete();
    if ($operation->operationSucceeded()) {
        printf('Deleted instance %s' . PHP_EOL, $instanceName);
    } else {
        $error = $operation->getError();
        printf('Failed to delete instance: %s' . PHP_EOL, $error->getMessage());
    }
}
# [END compute_instances_delete]

require_once __DIR__ . '/../../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
