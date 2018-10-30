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
use Google\ApiCore\ApiException;

function delete_instance($project_id, $instance_id)
{
    /**
     * Delete the Instance
     *
     * @param string $project_id Project id of the client.
     * @param string $instance_id Instance id of the client.
     */

    $instanceAdminClient = new BigtableInstanceAdminClient();

    $formattedInstance = $instanceAdminClient->instanceName($project_id, $instance_id);


    // [START bigtable_delete_instance]
    printf("Deleting Instance" . PHP_EOL);
    try {
        $instanceAdminClient->deleteInstance($formattedInstance);
        printf("Deleted Instance: %s." . PHP_EOL, $instance_id);
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf("Instance %s does not exists." . PHP_EOL, $instance_id);
        }
    }
    // [END bigtable_delete_instance]
}

$project_id = (isset($argv[1])) ? $argv[1] : getenv('PROJECT_ID');
$instance_id = (isset($argv[2])) ? $argv[2] : 'quickstart-instance-php';

delete_instance($project_id, $instance_id);