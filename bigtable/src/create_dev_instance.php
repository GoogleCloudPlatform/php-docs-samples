<?php
/**
 * Copyright 2019 Google LLC.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/bigtable/README.md
 */

namespace Google\Cloud\Samples\Bigtable;

// [START bigtable_create_dev_instance]
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\Instance;
use Google\Cloud\Bigtable\Admin\V2\Cluster;
use Google\Cloud\Bigtable\Admin\V2\StorageType;
use Google\Cloud\Bigtable\Admin\V2\Instance\Type as InstanceType;
use Google\ApiCore\ApiException;

/**
 * Create a development Bigtable instance
 *
 * @param string $projectId The Google Cloud project ID
 * @param string $instanceId The ID of the Bigtable instance to be generated
 * @param string $clusterId The ID of the cluster to be generated
 * @param string $locationId The Bigtable region ID where you want your instance to reside
 */
function create_dev_instance(
    string $projectId,
    string $instanceId,
    string $clusterId,
    string $locationId = 'us-east1-b'
): void {
    $instanceAdminClient = new BigtableInstanceAdminClient();

    $projectName = $instanceAdminClient->projectName($projectId);
    $instanceName = $instanceAdminClient->instanceName($projectId, $instanceId);

    printf('Creating a DEVELOPMENT Instance' . PHP_EOL);
    // Set options to create an Instance

    $storageType = StorageType::HDD;
    $development = InstanceType::DEVELOPMENT;
    $labels = ['dev-label' => 'dev-label'];

    # Create instance with given options
    $instance = new Instance();
    $instance->setDisplayName($instanceId);
    $instance->setLabels($labels);
    $instance->setType($development);

    // Create cluster with given options
    $cluster = new Cluster();
    $cluster->setDefaultStorageType($storageType);
    $cluster->setLocation(
        $instanceAdminClient->locationName(
            $projectId,
            $locationId
        )
    );
    $clusters = [
        $clusterId => $cluster
    ];
    // Create development instance with given options
    try {
        $instanceAdminClient->getInstance($instanceName);
        printf('Instance %s already exists.' . PHP_EOL, $instanceId);
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf('Creating a development Instance: %s' . PHP_EOL, $instanceId);
            $operationResponse = $instanceAdminClient->createInstance(
                $projectName,
                $instanceId,
                $instance,
                $clusters
            );
            $operationResponse->pollUntilComplete();
            if (!$operationResponse->operationSucceeded()) {
                print('Error: ' . $operationResponse->getError()->getMessage());
            } else {
                printf('Instance %s created.' . PHP_EOL, $instanceId);
            }
        } else {
            throw $e;
        }
    }
    // [END bigtable_create_dev_instance]
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
