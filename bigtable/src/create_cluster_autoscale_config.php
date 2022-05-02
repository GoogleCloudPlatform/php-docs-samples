<?php
/**
 * Copyright 2022 Google LLC.
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

// [START bigtable_api_cluster_create_autoscaling]
use Google\ApiCore\ApiException;
use Google\Cloud\Bigtable\Admin\V2\AutoscalingLimits;
use Google\Cloud\Bigtable\Admin\V2\AutoscalingTargets;
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\Cluster;
use Google\Cloud\Bigtable\Admin\V2\Cluster\ClusterAutoscalingConfig;
use Google\Cloud\Bigtable\Admin\V2\Cluster\ClusterConfig;
use Google\Cloud\Bigtable\Admin\V2\StorageType;

/**
 * Create a new autoscaling cluster in an existing Bigtable instance
 *
 * @param string $projectId The Google Cloud project ID
 * @param string $instanceId The ID of the Bigtable instance
 * @param string $clusterId The ID of the cluster to be updated
 * @param string $locationId The Bigtable region ID where you want your cluster to reside
 */
function create_cluster_autoscale_config(
    string $projectId,
    string $instanceId,
    string $clusterId,
    string $locationId = 'us-east1-b'
): void {
    $autoscaling_limits = new AutoscalingLimits([
        'min_serve_nodes' => 2,
        'max_serve_nodes' => 5,
    ]);
    $autoscaling_targets = new AutoscalingTargets([
        'cpu_utilization_percent' => 10,
    ]);
    $cluster_autoscale_config = new ClusterAutoscalingConfig([
        'autoscaling_limits' => $autoscaling_limits,
        'autoscaling_targets' => $autoscaling_targets,
    ]);
    $cluster_config = new ClusterConfig([
        'cluster_autoscaling_config' => $cluster_autoscale_config,
    ]);

    $instanceAdminClient = new BigtableInstanceAdminClient();

    $instanceName = $instanceAdminClient->instanceName($projectId, $instanceId);
    $clusterName = $instanceAdminClient->clusterName($projectId, $instanceId, $clusterId);

    printf('Adding Cluster to Instance %s' . PHP_EOL, $instanceId);
    try {
        $instanceAdminClient->getInstance($instanceName);
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

    $clustersBefore = $instanceAdminClient->listClusters($instanceName)->getClusters();
    $clusters = $clustersBefore->getIterator();
    foreach ($clusters as $cluster) {
        print($cluster->getName() . PHP_EOL);
    }

    $cluster = new Cluster();
    // if both serve nodes and autoscaling are set
    // the server will silently ignore the serve nodes
    // and use auto scaling functionality
    // $cluster->setServeNodes($newNumNodes);

    $cluster->setDefaultStorageType($storage_type);
    $cluster->setLocation(
        $instanceAdminClient->locationName(
            $projectId,
            $locationId
        )
    );
    $cluster->setClusterConfig($cluster_config);
    try {
        $instanceAdminClient->getCluster($clusterName);
        printf('Cluster %s already exists, aborting...', $clusterId);
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            $operationResponse = $instanceAdminClient->createCluster($instanceName, $clusterId, $cluster);

            $operationResponse->pollUntilComplete();
            if ($operationResponse->operationSucceeded()) {
                $result = $operationResponse->getResult();
                printf('Cluster created: %s', $clusterId);
            } else {
                $error = $operationResponse->getError();
                printf('Cluster not created: %s', $error->getMessage());
            }
        } else {
            throw $e;
        }
    }
}
// [END bigtable_api_cluster_create_autoscaling]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
