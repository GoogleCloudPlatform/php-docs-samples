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
use Google\Cloud\Bigtable\Admin\V2\AutoscalingLimits;
use Google\Cloud\Bigtable\Admin\V2\AutoscalingTargets;
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\Cluster;
use Google\Cloud\Bigtable\Admin\V2\Cluster\ClusterAutoscalingConfig;
use Google\Cloud\Bigtable\Admin\V2\Cluster\ClusterConfig;
use Google\Cloud\Bigtable\Admin\V2\StorageType;

/**
 * Creates a new autoscaling cluster in an existing Bigtable instance.
 *
 * @param string $projectId The Google Cloud project ID
 * @param string $instanceId The ID of the Bigtable instance
 * @param string $clusterId The ID of the cluster to be created
 * @param string $locationId The Bigtable region ID where you want your cluster to reside
 */
function create_cluster_autoscale_config(
    string $projectId,
    string $instanceId,
    string $clusterId,
    string $locationId = 'us-east1-b'
): void {
    $instanceAdminClient = new BigtableInstanceAdminClient();
    $autoscalingLimits = new AutoscalingLimits([
        'min_serve_nodes' => 2,
        'max_serve_nodes' => 5,
    ]);
    $autoscalingTargets = new AutoscalingTargets([
        'cpu_utilization_percent' => 10,
    ]);
    $clusterAutoscaleConfig = new ClusterAutoscalingConfig([
        'autoscaling_limits' => $autoscalingLimits,
        'autoscaling_targets' => $autoscalingTargets,
    ]);

    $clusterConfig = new ClusterConfig([
        'cluster_autoscaling_config' => $clusterAutoscaleConfig,
    ]);

    $instanceName = $instanceAdminClient->instanceName($projectId, $instanceId);
    printf('Adding Cluster to Instance %s' . PHP_EOL, $instanceId);
    $cluster = new Cluster();

    // if both serve nodes and autoscaling are set
    // the server will silently ignore the serve nodes
    // and use auto scaling functionality
    // $cluster->setServeNodes($newNumNodes);
    $cluster->setDefaultStorageType(StorageType::SSD);
    $cluster->setLocation(
        $instanceAdminClient->locationName(
            $projectId,
            $locationId
        )
    );
    $cluster->setClusterConfig($clusterConfig);
    $operationResponse = $instanceAdminClient->createCluster($instanceName, $clusterId, $cluster);

    $operationResponse->pollUntilComplete();
    if ($operationResponse->operationSucceeded()) {
        $result = $operationResponse->getResult();
        printf('Cluster created: %s' . PHP_EOL, $clusterId);
    } else {
        $error = $operationResponse->getError();
        printf('Cluster not created: %s' . PHP_EOL, $error->getMessage());
    }
}
// [END bigtable_api_cluster_create_autoscaling]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
