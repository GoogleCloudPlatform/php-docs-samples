<?php
/**
 * Copyright 2021 Google LLC.
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

// [START bigtable_update_cluster]
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\ApiCore\ApiException;

/**
 * Update a cluster in a Bigtable instance
 *
 * @param string $projectId The Google Cloud project ID
 * @param string $instanceId The ID of the Bigtable instance
 * @param string $clusterId The ID of the cluster to be updated
 * @param int $newNumNodes The updated number of serve nodes the cluster should have
 */
function update_cluster(
    string $projectId,
    string $instanceId,
    string $clusterId,
    int $newNumNodes
): void {
    $instanceAdminClient = new BigtableInstanceAdminClient();
    $clusterName = $instanceAdminClient->clusterName($projectId, $instanceId, $clusterId);

    try {
        $operationResponse = $instanceAdminClient->updateCluster($clusterName, $newNumNodes);

        $operationResponse->pollUntilComplete();
        if ($operationResponse->operationSucceeded()) {
            $updatedCluster = $operationResponse->getResult();
            printf('Cluster updated with the new num of nodes: %s.' . PHP_EOL, $updatedCluster->getServeNodes());
        // doSomethingWith($updatedCluster)
        } else {
            $error = $operationResponse->getError();
            // handleError($error)
        }
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf('Cluster %s does not exist.' . PHP_EOL, $clusterId);
            return;
        }
        throw $e;
    }
}
// [END bigtable_update_cluster]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
