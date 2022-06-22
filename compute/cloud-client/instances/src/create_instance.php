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

# [START compute_instances_create]
use Google\Cloud\Compute\V1\InstancesClient;
use Google\Cloud\Compute\V1\AttachedDisk;
use Google\Cloud\Compute\V1\AttachedDiskInitializeParams;
use Google\Cloud\Compute\V1\Instance;
use Google\Cloud\Compute\V1\NetworkInterface;

/**
 * To correctly handle string enums in Cloud Compute library
 * use constants defined in the Enums subfolder.
 */
use Google\Cloud\Compute\V1\Enums\AttachedDisk\Type;

/**
 * Creates an instance in the specified project and zone.
 *
 * @param string $projectId Project ID of the Cloud project to create the instance in.
 * @param string $zone Zone to create the instance in (like "us-central1-a").
 * @param string $instanceName Unique name for this Compute Engine instance.
 * @param string $machineType Machine type of the instance being created.
 * @param string $sourceImage Boot disk image name or family.
 * @param string $networkName Network interface to associate with the instance.
 *
 * @throws \Google\ApiCore\ApiException if the remote call fails.
 * @throws \Google\ApiCore\ValidationException if local error occurs before remote call.
 */
function create_instance(
    string $projectId,
    string $zone,
    string $instanceName,
    string $machineType = 'n1-standard-1',
    string $sourceImage = 'projects/debian-cloud/global/images/family/debian-11',
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
        ->setType(Type::PERSISTENT)
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

    # [START compute_instances_operation_check]
    // Wait for the operation to complete.
    $operation->pollUntilComplete();
    if ($operation->operationSucceeded()) {
        printf('Created instance %s' . PHP_EOL, $instanceName);
    } else {
        $error = $operation->getError();
        printf('Instance creation failed: %s' . PHP_EOL, $error->getMessage());
    }
    # [END compute_instances_operation_check]
}
# [END compute_instances_create]

require_once __DIR__ . '/../../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
