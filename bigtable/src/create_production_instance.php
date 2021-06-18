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

if (count($argv) < 3 || count($argv) > 4) {
    return printf("Usage: php %s PROJECT_ID INSTANCE_ID CLUSTER_ID [LOCATION_ID]" . PHP_EOL, __FILE__);
}
list($_, $projectId, $instanceId, $clusterId) = $argv;
$locationId = isset($argv[4]) ? $argv[4] : 'us-east1-b';

// [START bigtable_create_prod_instance]

use Google\Cloud\Bigtable\Admin\V2\Instance\Type as InstanceType;
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\StorageType;
use Google\Cloud\Bigtable\Admin\V2\Instance;
use Google\Cloud\Bigtable\Admin\V2\Cluster;
use Google\ApiCore\ApiException;

/** Uncomment and populate these variables in your code */
// $projectId = 'The Google project ID';
// $instanceId = 'The Bigtable instance ID';
// $clusterId = 'The Bigtable table ID';
// $locationId = 'The Bigtable region ID';

$instanceAdminClient = new BigtableInstanceAdminClient();

$projectName = $instanceAdminClient->projectName($projectId);
$instanceName = $instanceAdminClient->instanceName($projectId, $instanceId);

$serveNodes = 3;
$storageType = StorageType::SSD;
$production = InstanceType::PRODUCTION;
$labels = ['prod-label' => 'prod-label'];

$instance = new Instance();
$instance->setDisplayName($instanceId);

$instance->setLabels($labels);
$instance->setType($production);

$cluster = new Cluster();
$cluster->setDefaultStorageType($storageType);
$locationName = $instanceAdminClient->locationName($projectId, $locationId);
$cluster->setLocation($locationName);
$cluster->setServeNodes($serveNodes);
$clusters = [
    $clusterId => $cluster
];
try {
    $instanceAdminClient->getInstance($instanceName);
    printf("Instance %s already exists." . PHP_EOL, $instanceId);
    throw new Exception(sprintf("Instance %s already exists." . PHP_EOL, $instanceId));
} catch (ApiException $e) {
    if ($e->getStatus() === 'NOT_FOUND') {
        printf("Creating an Instance: %s" . PHP_EOL, $instanceId);
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
// [END bigtable_create_prod_instance]
