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

// [START compute_create_instance]
use Google\Cloud\Compute\V1\InstancesClient;
use Google\Cloud\Compute\V1\AttachedDisk;
use Google\Cloud\Compute\V1\AttachedDiskInitializeParams;
use Google\Cloud\Compute\V1\Instance;
use Google\Cloud\Compute\V1\NetworkInterface;

/**
 * Creates an instance.
 * Example:
 * ```
 * create_instance($projectId, $zone, $instanceName);
 * ```
 *
 * @param string $projectId Your Google Cloud project ID.
 * @param string $zone The zone to create the instance in (e.g. "us-central1-a")
 * @param string $instanceName The unique name for this Compute instance.
 * @param string $machineType Instance machine type
 * @param string $sourceImage Boot disk image name or family
 * @param string $networkName The Compute instance ID.
 */
function create_instance(
    string $projectId,
    string $zone,
    string $instanceName,
    string $machineType = 'n1-standard-1',
    string $sourceImage = 'projects/debian-cloud/global/images/family/debian-10',
    string $networkName = 'global/networks/default'
) {
    // Set the machine type using the specified zone
    $machineTypeFullName = sprintf('zones/%s/machineTypes/%s', $zone, $machineType);

    // Set the boot disk
    $diskInitializeParams = (new AttachedDiskInitializeParams())
        ->setSourceImage($sourceImage);
    $disk = (new AttachedDisk())
        ->setBoot(true)
        ->setInitializeParams($diskInitializeParams);

    // Set the network
    $network = (new NetworkInterface())
        ->setName($networkName);

    // Create the Instance message
    $instance = (new Instance())
        ->setName($instanceName)
        ->setDisks([$disk])
        ->setMachineType($machineTypeFullName)
        ->setNetworkInterfaces([$network]);

    // Insert the new Compute Engine instance using the InstancesClient
    $instancesClient = new InstancesClient();
    $operation = $instancesClient->insert($instance, $projectId, $zone);

    /** TODO: wait until operation completes */

    printf('Created instance %s' . PHP_EOL, $instanceName);
}
// [END compute_create_instance]

require_once __DIR__ . '/../../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
