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
list($_, $project_id, $instance_id, $cluster_id) = $argv;
$location_id = isset($argv[4]) ? $argv[4] : 'us-east1-b';

// [START bigtable_create_prod_instance]

use Google\Cloud\Bigtable\Admin\V2\Instance\Type as InstanceType;
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\StorageType;
use Google\Cloud\Bigtable\Admin\V2\Instance;
use Google\Cloud\Bigtable\Admin\V2\Cluster;
use Google\ApiCore\ApiException;

/** Uncomment and populate these variables in your code */
// $project_id = 'The Google project ID';
// $instance_id = 'The Bigtable instance ID';
// $cluster_id = 'The Bigtable table ID';
// $location_id = 'The Bigtable region ID';

$instanceAdminClient = new BigtableInstanceAdminClient();

$projectName = $instanceAdminClient->projectName($project_id);
$instanceName = $instanceAdminClient->instanceName($project_id, $instance_id);

$serve_nodes = 3;
$storage_type = StorageType::SSD;
$production = InstanceType::PRODUCTION;
$labels = ['prod-label' => 'prod-label'];

$instance = new Instance();
$instance->setDisplayName($instance_id);

$instance->setLabels($labels);
$instance->setType($production);

$cluster = new Cluster();
$cluster->setDefaultStorageType($storage_type);
$locationName = $instanceAdminClient->locationName($project_id, $location_id);
$cluster->setLocation($locationName);
$cluster->setServeNodes($serve_nodes);
$clusters = [
    $cluster_id => $cluster
];
try {
    $instanceAdminClient->getInstance($instanceName);
    printf("Instance %s already exists." . PHP_EOL, $instance_id);
    throw new Exception(sprintf("Instance %s already exists." . PHP_EOL, $instance_id));
} catch (ApiException $e) {
    if ($e->getStatus() === 'NOT_FOUND') {
        printf("Creating an Instance: %s" . PHP_EOL, $instance_id);
        $operationResponse = $instanceAdminClient->createInstance(
            $projectName,
            $instance_id,
            $instance,
            $clusters
        );
        $operationResponse->pollUntilComplete();
        if (!$operationResponse->operationSucceeded()) {
            print('Error: ' . $operationResponse->getError()->getMessage());
        } else {
            printf("Instance %s created.", $instance_id);
        }
    } else {
        throw $e;
    }
}
// [END bigtable_create_prod_instance]
