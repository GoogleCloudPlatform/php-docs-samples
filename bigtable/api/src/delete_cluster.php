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
use Google\ApiCore\ApiException;

$project_id = (isset($argv[1])) ? $argv[1] : getenv('PROJECT_ID');
$instance_id = (isset($argv[2])) ? $argv[2] : 'quickstart-instance-php';
$cluster_id = (isset($argv[3])) ? $argv[3] : 'php-cluster-d';
/** Uncomment and populate these variables in your code */
// $project_id = 'The Google project ID';
// $instance_id = 'The Bigtable instance ID';
// $cluster_id = 'The Bigtable table ID';


$instanceAdminClient = new BigtableInstanceAdminClient();

$clusterName = $instanceAdminClient->clusterName($project_id, $instance_id, $cluster_id);

// [START bigtable_delete_cluster]
printf("Deleting Cluster" . PHP_EOL);
try {
    $instanceAdminClient->deleteCluster($clusterName);
    printf("Cluster %s does not exist." . PHP_EOL, $cluster_id);
} catch (ApiException $e) {
    if ($e->getStatus() === 'NOT_FOUND') {
        printf("Cluster %s deleted." . PHP_EOL, $cluster_id);
    }
}
// [END bigtable_delete_cluster]
