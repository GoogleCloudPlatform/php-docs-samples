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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/bigtable/README.md
 */

namespace Google\Cloud\Samples\Bigtable;

// [START bigtable_create_cluster]
use Google\ApiCore\ApiException;
use Google\Cloud\Bigtable\Admin\V2\Client\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\Cluster;
use Google\Cloud\Bigtable\Admin\V2\CreateClusterRequest;
use Google\Cloud\Bigtable\Admin\V2\GetClusterRequest;
use Google\Cloud\Bigtable\Admin\V2\GetInstanceRequest;
use Google\Cloud\Bigtable\Admin\V2\ListClustersRequest;
use Google\Cloud\Bigtable\Admin\V2\StorageType;

/**
 * Create a cluster in an existing Bigtable instance
 *
 * @param string $projectId The Google Cloud project ID
 * @param string $instanceId The ID of the parent Bigtable instance
 * @param string $clusterId The ID of the cluster to be generated
 * @param string $locationId The Bigtable region ID where you want your cluster to reside
 */
function create_cluster(
    string $projectId,
    string $instanceId,
    string $clusterId,
    string $locationId = 'us-east1-b'
): void {
    $instanceAdminClient = new BigtableInstanceAdminClient();

    $instanceName = $instanceAdminClient->instanceName($projectId, $instanceId);
    $clusterName = $instanceAdminClient->clusterName($projectId, $instanceId, $clusterId);

    printf('Adding Cluster to Instance %s' . PHP_EOL, $instanceId);
    try {
        $getInstanceRequest = (new GetInstanceRequest())
            ->setName($instanceName);
        $instanceAdminClient->getInstance($getInstanceRequest);
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf('Instance %s does not exists.' . PHP_EOL, $instanceId);
            return;
        } else {
            throw $e;
        }
    }
    printf('Listing Clusters:' . PHP_EOL);

    $storage_type = StorageType::SSD;
    $serve_nodes = 3;
    $listClustersRequest = (new ListClustersRequest())
        ->setParent($instanceName);

    $clustersBefore = $instanceAdminClient->listClusters($listClustersRequest)->getClusters();
    $clusters = $clustersBefore->getIterator();
    foreach ($clusters as $cluster) {
        print($cluster->getName() . PHP_EOL);
    }

    $cluster = new Cluster();
    $cluster->setServeNodes($serve_nodes);
    $cluster->setDefaultStorageType($storage_type);
    $cluster->setLocation(
        $instanceAdminClient->locationName(
            $projectId,
            $locationId
        )
    );
    try {
        $getClusterRequest = (new GetClusterRequest())
            ->setName($clusterName);
        $instanceAdminClient->getCluster($getClusterRequest);
        printf('Cluster %s already exists, aborting...', $clusterId);
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            $createClusterRequest = (new CreateClusterRequest())
                ->setParent($instanceName)
                ->setClusterId($clusterId)
                ->setCluster($cluster);
            $operationResponse = $instanceAdminClient->createCluster($createClusterRequest);

            $operationResponse->pollUntilComplete();
            if ($operationResponse->operationSucceeded()) {
                $result = $operationResponse->getResult();
                printf('Cluster created: %s', $clusterId);
            } else {
                $error = $operationResponse->getError();
                printf('Cluster not created: %s', $error?->getMessage());
            }
        } else {
            throw $e;
        }
    }
}
// [END bigtable_create_cluster]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
