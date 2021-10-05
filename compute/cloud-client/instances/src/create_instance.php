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

# [START compute_instances_create]
use Google\Cloud\Compute\V1\InstancesClient;
use Google\Cloud\Compute\V1\AttachedDisk;
use Google\Cloud\Compute\V1\AttachedDiskInitializeParams;
use Google\Cloud\Compute\V1\Instance;
use Google\Cloud\Compute\V1\NetworkInterface;
use Google\Cloud\Compute\V1\Operation;

/**
 * Create an instance in the specified project and zone.
 * Example:
 * ```
 * create_instance($projectId, $zone, $instanceName);
 * ```
 *
 * @param string $projectId Project ID of the Cloud project to create the instance in.
 * @param string $zone Zone to create the instance in (like "us-central1-a").
 * @param string $instanceName Unique name for this Compute Engine instance.
 * @param string $machineType Machine type of the instance being created.
 * @param string $sourceImage Boot disk image name or family.
 * @param string $networkName Network interface to associate with the instance.
 *
 * @throws \Google\ApiCore\ApiException if the remote call fails.
 */
function create_instance(
    string $projectId,
    string $zone,
    string $instanceName,
    string $machineType = 'n1-standard-1',
    string $sourceImage = 'projects/debian-cloud/global/images/family/debian-10',
    string $networkName = 'global/networks/default'
) {
    // Set the machine type using the specified zone.
    $machineTypeFullName = sprintf('zones/%s/machineTypes/%s', $zone, $machineType);

    // Describe the source image of the boot disk to attach to the instance.
    $diskInitializeParams = (new AttachedDiskInitializeParams())
        ->setSourceImage($sourceImage);
    $disk = (new AttachedDisk())
        ->setBoot(true)
        ->setAutoDelete(true)
        ->setInitializeParams($diskInitializeParams);

    // Use the network interface provided in the $networkName argument.
    $network = (new NetworkInterface())
        ->setName($networkName);

    // Create the Instance object.
    $instance = (new Instance())
        ->setName($instanceName)
        ->setDisks([$disk])
        ->setMachineType($machineTypeFullName)
        ->setNetworkInterfaces([$network]);

    // Insert the new Compute Engine instance using InstancesClient.
    $instancesClient = new InstancesClient();
    $operation = $instancesClient->insert($instance, $projectId, $zone);

    // Wait for the create operation to complete using a custom helper function.
    // @see src/wait_for_operation.php
    $operation = wait_for_operation($operation, $projectId, $zone);
    if (empty($operation->getError())) {
        printf('Created instance %s' . PHP_EOL, $instanceName);
    } else {
        printf('Instance creation failed!' . PHP_EOL);
    }
}
# [END compute_instances_create]

require_once __DIR__ . '/../../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
