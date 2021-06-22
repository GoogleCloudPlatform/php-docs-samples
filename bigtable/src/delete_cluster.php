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

if (count($argv) != 4) {
    return printf("Usage: php %s PROJECT_ID INSTANCE_ID CLUSTER_ID" . PHP_EOL, __FILE__);
}
list($_, $projectId, $instanceId, $clusterId) = $argv;

// [START bigtable_delete_cluster]

use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\ApiCore\ApiException;

/** Uncomment and populate these variables in your code */
// $projectId = 'The Google project ID';
// $instanceId = 'The Bigtable instance ID';
// $clusterId = 'The Bigtable cluster ID';


$instanceAdminClient = new BigtableInstanceAdminClient();

$clusterName = $instanceAdminClient->clusterName($projectId, $instanceId, $clusterId);


printf("Deleting Cluster" . PHP_EOL);
try {
    $instanceAdminClient->deleteCluster($clusterName);
    printf("Cluster %s deleted." . PHP_EOL, $clusterId);
} catch (ApiException $e) {
    if ($e->getStatus() === 'NOT_FOUND') {
        printf("Cluster %s does not exist." . PHP_EOL, $clusterId);
    } else {
        throw $e;
    }
}
// [END bigtable_delete_cluster]
