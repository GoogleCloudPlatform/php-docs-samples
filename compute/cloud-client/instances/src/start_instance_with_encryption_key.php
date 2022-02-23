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

# [START compute_start_enc_instance]
use Google\Cloud\Compute\V1\CustomerEncryptionKey;
use Google\Cloud\Compute\V1\CustomerEncryptionKeyProtectedDisk;
use Google\Cloud\Compute\V1\InstancesClient;
use Google\Cloud\Compute\V1\InstancesStartWithEncryptionKeyRequest;

/**
 * Starts a stopped Google Compute Engine instance (with encrypted disks).
 *
 * @param string $projectId Project ID or project number of the Cloud project your instance belongs to.
 * @param string $zone Name of the zone your instance belongs to.
 * @param string $instanceName Name of the instance you want to stop.
 * @param string $key Bytes object representing a raw base64 encoded key to your instance's boot disk.
 *                    For more information about disk encryption see:
 *                    https://cloud.google.com/compute/docs/disks/customer-supplied-encryption#specifications
 *
 * @throws \Google\ApiCore\ApiException if the remote call fails.
 * @throws \Google\ApiCore\ValidationException if local error occurs before remote call.
 */
function start_instance_with_encryption_key(
    string $projectId,
    string $zone,
    string $instanceName,
    string $key
) {
    // Initiate the InstancesClient.
    $instancesClient = new InstancesClient();

    // Get data about the instance.
    $instanceData = $instancesClient->get($instanceName, $projectId, $zone);

    // Use `setRawKey` to send over the key to unlock the disk
    // To use a key stored in KMS, you need to use `setKmsKeyName` and `setKmsKeyServiceAccount`
    $customerEncryptionKey = (new CustomerEncryptionKey())
        ->setRawKey($key);

    // Prepare the information about disk encryption.
    $diskData = (new CustomerEncryptionKeyProtectedDisk())
        ->setSource($instanceData->getDisks()[0]->getSource())
        ->setDiskEncryptionKey($customerEncryptionKey);

    // Set request with one disk.
    $instancesStartWithEncryptionKeyRequest = (new InstancesStartWithEncryptionKeyRequest())
        ->setDisks(array($diskData));

    // Start the instance with encrypted disk.
    $operation = $instancesClient->startWithEncryptionKey($instanceName, $instancesStartWithEncryptionKeyRequest, $projectId, $zone);

    // Wait for the operation to complete.
    $operation->pollUntilComplete();
    if ($operation->operationSucceeded()) {
        printf('Instance %s started successfully' . PHP_EOL, $instanceName);
    } else {
        $error = $operation->getError();
        printf('Starting instance failed: %s' . PHP_EOL, $error->getMessage());
    }
}
# [END compute_start_enc_instance]

require_once __DIR__ . '/../../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
