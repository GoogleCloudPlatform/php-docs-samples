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

// Include Google Cloud dependencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) < 3 || count($argv) > 5) {
    return printf("Usage: php %s PROJECT_ID INSTANCE_ID CLUSTER_ID [LOCATION_ID]" . PHP_EOL, __FILE__);
}
list($_, $projectId, $instanceId, $clusterId) = $argv;
$locationId = isset($argv[4]) ? $argv[4] : 'us-east1-b';

// [START bigtable_create_dev_instance]

use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\Instance;
use Google\Cloud\Bigtable\Admin\V2\Cluster;
use Google\Cloud\Bigtable\Admin\V2\StorageType;
use Google\Cloud\Bigtable\Admin\V2\Instance\Type as InstanceType;
use Google\ApiCore\ApiException;

/** Uncomment and populate these variables in your code */
// $projectId = 'The Google project ID';
// $instanceId = 'The Bigtable instance ID';
// $clusterId = 'The Bigtable cluster ID';
// $locationId = 'The Bigtable region ID';


$instanceAdminClient = new BigtableInstanceAdminClient();

$projectName = $instanceAdminClient->projectName($projectId);
$instanceName = $instanceAdminClient->instanceName($projectId, $instanceId);


printf("Creating a DEVELOPMENT Instance" . PHP_EOL);
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
    printf("Instance %s already exists." . PHP_EOL, $instanceId);
} catch (ApiException $e) {
    if ($e->getStatus() === 'NOT_FOUND') {
        printf("Creating a development Instance: %s" . PHP_EOL, $instanceId);
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
            printf("Instance %s created.", $instanceId);
        }
    } else {
        throw $e;
    }
}
// [END bigtable_create_dev_instance]
