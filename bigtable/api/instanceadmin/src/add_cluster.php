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

/*
 * Demonstrates how to connect to Cloud Bigtable and run some basic operations.
 *
 * Prerequisites:
 *
 * - Create a Cloud Bigtable project.
 *   https://cloud.google.com/bigtable/docs/creating-cluster
 * - Set your Google Application Default Credentials.
 *   https://developers.google.com/identity/protocols/application-default-credentials
 *
 * Operations performed:
 * - Create a Cloud Bigtable Instance.
 * - List Instance for a Cloud Bigtable.
 * - Delete a Cloud Bigtable Instance.
 * - Create a Cloud Bigtable Cluster.
 * - List Cloud Bigtable Clusters.
 * - Delete a Cloud Bigtable Cluster.
 */


require_once __DIR__ . '/../vendor/autoload.php';


use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\Instance;
use Google\Cloud\Bigtable\Admin\V2\Cluster;
use Google\Cloud\Bigtable\Admin\V2\StorageType;
use Google\Cloud\Bigtable\Admin\V2\Instance\Type as InstanceType;
use Google\ApiCore\ApiException;

function add_cluster($project_id, $instance_id, $cluster_id)
{
    /**
     * Add Cluster
     *
     * @param string $project_id Project id of the client.
     * @param string $instance_id Instance id of the client.
     * @param string cluster_id Cluster id.
     */

    $instanceAdminClient = new BigtableInstanceAdminClient();

    $formattedInstance = $instanceAdminClient->instanceName($project_id, $instance_id);
    $formattedCluster = $instanceAdminClient->clusterName($project_id, $instance_id, $cluster_id);

    $instance_exists = true;
    try {
        $instanceAdminClient->getInstance($formattedInstance);
        printf("Adding Cluster to Instance %s" . PHP_EOL, $instance_id);
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf("Instance %s does not exists." . PHP_EOL, $instance_id);
            return;
        }
    }
    // [START bigtable_create_cluster]
    printf("Listing Clusters..." . PHP_EOL);

    $clusters_local = $instanceAdminClient->listClusters($formattedInstance)->getClusters();
    $clusters_array = iterator_to_array($clusters_local->getIterator());


    $location_id = 'us-east1-b';
    $storage_type = StorageType::SSD;
    $serve_nodes = 3;

    foreach ($clusters_array as $cluster) {
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
        $instanceAdminClient->getCluster($formattedCluster);
        printf("Cluster %s not created", $cluster_id);
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            $operationResponse = $instanceAdminClient->createCluster($formattedInstance, $cluster_id, $cluster);

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
}

$project_id = (isset($argv[1])) ? $argv[1] : getenv('PROJECT_ID');
$instance_id = (isset($argv[2])) ? $argv[2] : 'quickstart-instance-php';
$cluster_id = (isset($argv[3])) ? $argv[3] :'php-cluster-d';

add_cluster($project_id, $instance_id, $cluster_id);