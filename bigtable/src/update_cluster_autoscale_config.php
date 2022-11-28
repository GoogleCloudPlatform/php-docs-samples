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

// [START bigtable_api_cluster_update_autoscaling]
use Google\ApiCore\ApiException;
use Google\Cloud\Bigtable\Admin\V2\AutoscalingLimits;
use Google\Cloud\Bigtable\Admin\V2\AutoscalingTargets;
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\Cluster\ClusterAutoscalingConfig;
use Google\Cloud\Bigtable\Admin\V2\Cluster\ClusterConfig;
use Google\Protobuf\FieldMask;

/**
 * Updates autoscale configurations for an existing Bigtable cluster.
 *
 * @param string $projectId The Google Cloud project ID
 * @param string $instanceId The ID of the Bigtable instance
 * @param string $clusterId The ID of the cluster to be updated
 */
function update_cluster_autoscale_config(
    string $projectId,
    string $instanceId,
    string $clusterId
): void {
    $instanceAdminClient = new BigtableInstanceAdminClient();
    $clusterName = $instanceAdminClient->clusterName($projectId, $instanceId, $clusterId);
    $cluster = $instanceAdminClient->getCluster($clusterName);

    $autoscalingLimits = new AutoscalingLimits([
        'min_serve_nodes' => 2,
        'max_serve_nodes' => 5,
    ]);
    $autoscalingTargets = new AutoscalingTargets([
        'cpu_utilization_percent' => 20,
    ]);
    $clusterAutoscaleConfig = new ClusterAutoscalingConfig([
        'autoscaling_limits' => $autoscalingLimits,
        'autoscaling_targets' => $autoscalingTargets,
    ]);
    $clusterConfig = new ClusterConfig([
        'cluster_autoscaling_config' => $clusterAutoscaleConfig,
    ]);

    $cluster->setClusterConfig($clusterConfig);

    $updateMask = new FieldMask([
        'paths' => [
          // if both serve nodes and autoscaling configs are set
          // the server will silently ignore the `serve_nodes` agument
          // 'serve_nodes',
          'cluster_config'
        ],
    ]);

    try {
        $operationResponse = $instanceAdminClient->partialUpdateCluster($cluster, $updateMask);

        $operationResponse->pollUntilComplete();
        if ($operationResponse->operationSucceeded()) {
            $updatedCluster = $operationResponse->getResult();
            printf('Cluster %s updated with autoscale config.' . PHP_EOL, $clusterId);
        } else {
            $error = $operationResponse->getError();
            printf('Cluster %s failed to update: %s.' . PHP_EOL, $clusterId, $error->getMessage());
        }
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf('Cluster %s does not exist.' . PHP_EOL, $clusterId);
            return;
        }
        throw $e;
    }
}
// [END bigtable_api_cluster_update_autoscaling]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
