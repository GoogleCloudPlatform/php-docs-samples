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

# [START compute_instances_create_encrypted]
use Google\Cloud\Compute\V1\CustomerEncryptionKey;
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
 * Creates an instance in the specified project and zone with encrypted disk that uses customer provided key.
 * Example:
 * ```
 * create_instance_with_encryption_key($projectId, $zone, $instanceName, $key);
 * ```
 *
 * @param string $projectId Project ID of the Cloud project to create the instance in.
 * @param string $zone Zone to create the instance in (like "us-central1-a").
 * @param string $instanceName Unique name for this Compute Engine instance.
 * @param string $key Bytes object representing a raw base64 encoded key to your instance's boot disk.
 *                    For more information about disk encryption see:
 *                    https://cloud.google.com/compute/docs/disks/customer-supplied-encryption#specifications
 * @param string $machineType Machine type of the instance being created.
 * @param string $sourceImage Boot disk image name or family.
 * @param string $networkName Network interface to associate with the instance.
 *
 * @throws \Google\ApiCore\ApiException if the remote call fails.
 * @throws \Google\ApiCore\ValidationException if local error occurs before remote call.
 */
function create_instance_with_encryption_key(
    string $projectId,
    string $zone,
    string $instanceName,
    string $key,
    string $machineType = 'n1-standard-1',
    string $sourceImage = 'projects/debian-cloud/global/images/family/debian-10',
    string $networkName = 'global/networks/default'
) {
    // Set the machine type using the specified zone.
    $machineTypeFullName = sprintf('zones/%s/machineTypes/%s', $zone, $machineType);

    // Describe the source image of the boot disk to attach to the instance.
    $diskInitializeParams = (new AttachedDiskInitializeParams())
        ->setSourceImage($sourceImage);

    // Use `setRawKey` to send over the key to unlock the disk
    // To use a key stored in KMS, you need to use `setKmsKeyName` and `setKmsKeyServiceAccount`
    $customerEncryptionKey = (new CustomerEncryptionKey())
        ->setRawKey($key);

    $disk = (new AttachedDisk())
        ->setBoot(true)
        ->setAutoDelete(true)
        ->setType(Type::PERSISTENT)
        ->setInitializeParams($diskInitializeParams)
        ->setDiskEncryptionKey($customerEncryptionKey);

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

    // Wait for the operation to complete.
    $operation->pollUntilComplete();
    if ($operation->operationSucceeded()) {
        printf('Created instance %s' . PHP_EOL, $instanceName);
    } else {
        $error = $operation->getError();
        printf('Instance creation failed: %s' . PHP_EOL, $error->getMessage());
    }
}
# [END compute_instances_create_encrypted]

require_once __DIR__ . '/../../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
