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


use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\Instance;
use Google\Cloud\Bigtable\Admin\V2\Cluster;
use Google\Cloud\Bigtable\Admin\V2\StorageType;
use Google\Cloud\Bigtable\Admin\V2\Instance\Type as InstanceType;
use Google\ApiCore\ApiException;


$project_id = (isset($argv[1])) ? $argv[1] : getenv('PROJECT_ID');
$instance_id = (isset($argv[2])) ? $argv[2] : 'quickstart-instance-php';
$cluster_id = (isset($argv[3])) ? $argv[3] : 'php-cluster-d';
$location_id = 'us-east1-b';
/** Uncomment and populate these variables in your code */
// $project_id = 'The Google project ID';
// $instance_id = 'The Bigtable instance ID';
// $cluster_id = 'The Bigtable table ID';
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
    }
}
// [START bigtable_create_cluster]
printf("Listing Clusters..." . PHP_EOL);

$storage_type = StorageType::SSD;
$serve_nodes = 3;

$clusters = $instanceAdminClient->listClusters($formattedInstance)->getClusters()->getIterator();
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
    printf("Cluster %s not created", $cluster_id);
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
    }
}
// [END bigtable_create_cluster]