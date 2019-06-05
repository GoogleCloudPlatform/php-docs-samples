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
list($_, $project_id, $instance_id, $cluster_id) = $argv;
$location_id = isset($argv[4]) ? $argv[4] : 'us-east1-b';

// [START bigtable_create_cluster]

use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\Cluster;
use Google\Cloud\Bigtable\Admin\V2\StorageType;
use Google\ApiCore\ApiException;

/** Uncomment and populate these variables in your code */
// $project_id = 'The Google project ID';
// $instance_id = 'The Bigtable instance ID';
// $cluster_id = 'The Bigtable cluster ID';
// $location_id = 'The Bigtable region ID';


$instanceAdminClient = new BigtableInstanceAdminClient();

$instanceName = $instanceAdminClient->instanceName($project_id, $instance_id);
$clusterName = $instanceAdminClient->clusterName($project_id, $instance_id, $cluster_id);

printf("Adding Cluster to Instance %s" . PHP_EOL, $instance_id);
try {
    $instanceAdminClient->getInstance($instanceName);
} catch (ApiException $e) {
    if ($e->getStatus() === 'NOT_FOUND') {
        printf("Instance %s does not exists." . PHP_EOL, $instance_id);
        return;
    } else {
        throw $e;
    }
}
printf("Listing Clusters:" . PHP_EOL);

$storage_type = StorageType::SSD;
$serve_nodes = 3;

$clustersBefore = $instanceAdminClient->listClusters($instanceName)->getClusters();
$clusters = $clustersBefore->getIterator();
foreach ($clusters as $cluster) {
    print($cluster->getName() . PHP_EOL);
}

$cluster = new Cluster();
$cluster->setServeNodes($serve_nodes);
$cluster->setDefaultStorageType($storage_type);
$cluster->setLocation(
    $instanceAdminClient->locationName(
        $project_id,
        $location_id
    )
);
try {
    $instanceAdminClient->getCluster($clusterName);
    printf("Cluster %s already exists, aborting...", $cluster_id);
} catch (ApiException $e) {
    if ($e->getStatus() === 'NOT_FOUND') {
        $operationResponse = $instanceAdminClient->createCluster($instanceName, $cluster_id, $cluster);

        $operationResponse->pollUntilComplete();
        if ($operationResponse->operationSucceeded()) {
            $result = $operationResponse->getResult();
            printf("Cluster created: %s", $cluster_id);
        } else {
            $error = $operationResponse->getError();
            printf("Cluster not created: %s", $error);
        }
    } else {
        throw $e;
    }
}
// [END bigtable_create_cluster]
