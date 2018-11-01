<?php

/**
 * Copyright 2018 Google LLC.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/bigtable/api/README.md
 */

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) < 3 || count($argv) > 4) {
    return printf("Usage: php %s PROJECT_ID INSTANCE_ID CLUSTER_ID [LOCATION_ID]".PHP_EOL, __FILE__);
}
list($_, $project_id, $instance_id, $cluster_id) = $argv;
$location_id = isset($argv[4])?$argv[4]:'us-east1-b';

use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\Instance;
use Google\Cloud\Bigtable\Admin\V2\Cluster;
use Google\Cloud\Bigtable\Admin\V2\StorageType;
use Google\Cloud\Bigtable\Admin\V2\Instance\Type as InstanceType;
use Google\ApiCore\ApiException;

/** Uncomment and populate these variables in your code */
// $project_id = 'The Google project ID';
// $instance_id = 'The Bigtable instance ID';
// $cluster_id = 'The Bigtable table ID';
// $location_id = 'The Bigtable region ID';

$instanceAdminClient = new BigtableInstanceAdminClient();

$instanceName = $instanceAdminClient->instanceName($project_id, $instance_id);

$instance = new Instance();
$instance->setDisplayName($instance_id);
$instance->setName($instanceName);

$serve_nodes = 3;
$storage_type = StorageType::SSD;
$production = InstanceType::PRODUCTION;
$labels = ['prod-label' => 'prod-label'];

$projectName = $instanceAdminClient->projectName($project_id);

$instance = new Instance();
$instance->setDisplayName($instance_id);

$instance->setLabels($labels);
$instance->setType($production);

// [START bigtable_check_instance_exists]
try {
    $instanceAdminClient->getInstance($instanceName);
    printf("Instance %s already exists." . PHP_EOL, $instance_id);
} catch (ApiException $e) {
    if ($e->getStatus() === 'NOT_FOUND') {
        printf("Instance %s does not exists." . PHP_EOL, $instance_id);
    }
}
// [END bigtable_check_instance_exists]

// [START bigtable_create_prod_instance]
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
} catch (ApiException $e) {
    if ($e->getStatus() === 'NOT_FOUND') {
        printf("Creating an Instance:" . PHP_EOL);
        $operationResponse = $instanceAdminClient->createInstance(
            $projectName,
            $instance_id,
            $instance,
            $clusters
        );
        $operationResponse->pollUntilComplete();
        if (!$operationResponse->operationSucceeded()) {
            throw new Exception('error creating instance', -1);
        }
    }
}
// [END bigtable_create_prod_instance]

// [START bigtable_list_instances]
printf("Listing Instances:" . PHP_EOL);
$instances = $instanceAdminClient->listInstances($projectName)->getInstances()->getIterator();
foreach ($instances as $instance) {
    print($instance->getDisplayName() . PHP_EOL);
}
// [END bigtable_list_instances]
// [START bigtable_get_instance]
$labels = json_encode(iterator_to_array($instance->getLabels()->getIterator()));
printf('Name of instance: %s' . PHP_EOL, $instance->getDisplayName());
printf('Labels: %s' . PHP_EOL, $labels);
// [END bigtable_get_instance]
// [START bigtable_get_clusters]
printf("Listing Clusters..." . PHP_EOL);
$clusters = $instanceAdminClient->listClusters($instanceName)->getClusters()->getIterator();

foreach ($clusters as $cluster) {
    print($cluster->getName() . PHP_EOL);
}
// [END bigtable_get_clusters]
