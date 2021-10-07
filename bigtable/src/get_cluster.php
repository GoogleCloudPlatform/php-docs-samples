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

// [START bigtable_get_cluster]
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\StorageType;
use Google\Cloud\Bigtable\Admin\V2\Instance\State;
use Google\ApiCore\ApiException;

/**
 * Get a Bigtable cluster
 *
 * @param string $projectId The Google Cloud project ID
 * @param string $instanceId The ID of the Bigtable instance
 * @param string $clusterId The ID of the cluster to fetch
 */
function get_cluster(
    string $projectId,
    string $instanceId,
    string $clusterId
): void {
    $instanceAdminClient = new BigtableInstanceAdminClient();

    printf('Fetching the Cluster %s' . PHP_EOL, $clusterId);
    try {
        $clusterName = $instanceAdminClient->clusterName($projectId, $instanceId, $clusterId);
        $cluster = $instanceAdminClient->getCluster($clusterName);
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf('Cluster %s does not exists.' . PHP_EOL, $clusterId);
            return;
        }
        throw $e;
    }

    printf('Printing Details:' . PHP_EOL);

    // Fetch some commonly used metadata
    printf('Name: ' . $cluster->getName() . PHP_EOL);
    printf('Location: ' . $cluster->getLocation() . PHP_EOL);
    printf('State: ' . State::name($cluster->getState()) . PHP_EOL);
    printf('Default Storage Type: ' . StorageType::name($cluster->getDefaultStorageType()) . PHP_EOL);
    printf('Nodes: ' . $cluster->getServeNodes() . PHP_EOL);
    printf('Encryption Config: ' . ($cluster->hasEncryptionConfig() ? $cluster->getEncryptionConfig()->getKmsKeyName() : 'N/A') . PHP_EOL);
}
// [END bigtable_get_cluster]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
