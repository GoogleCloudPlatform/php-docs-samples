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

function create_dev_instance($project_id, $instance_id, $cluster_id)
{
    /**
     * Creates a Development instance with the name "hdd-instance"
     * * location us-central1-f
     * * Cluster nodes should not be set while creating Develpment
     * * instance
     *
     * @param string $project_id Project id of the client.
     * @param string $instance_id Instance id of the client.
     */

    $instanceAdminClient = new BigtableInstanceAdminClient();

    $formattedInstance = $instanceAdminClient->instanceName($project_id, $instance_id);

    // [START bigtable_create_dev_instance]
    printf("Creating a DEVELOPMENT Instance" . PHP_EOL);
    // Set options to create an Instance

    $location_id = 'us-east1-b';
    $storage_type = StorageType::HDD;
    $development = InstanceType::DEVELOPMENT;
    $labels = ['dev-label' => 'dev-label'];

    $instance = new Instance();
    $instance->setDisplayName($instance_id);
    $instance->setName($formattedInstance);

    $formattedParent = $instanceAdminClient->projectName($project_id);

    # Create instance with given options
    $instance = new Instance();
    $instance->setDisplayName($instance_id);
    $instance->setLabels($labels);
    $instance->setType($development);

    try {
        $instanceAdminClient->getInstance($formattedInstance);
        printf("Instance %s already exists." . PHP_EOL, $instance_id);
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf("Instance %s does not exists." . PHP_EOL, $instance_id);
        }
    }

    // Create cluster with given options
    $cluster = new Cluster();
    $cluster->setDefaultStorageType($storage_type);
    $cluster->setLocation(
        $instanceAdminClient->locationName(
            $project_id,
            $location_id
        )
    );
    $clusters = [
        $cluster_id => $cluster
    ];
    // Create development instance with given options
    try {
        $instanceAdminClient->getInstance($formattedInstance);
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf("Creating an Instance" . PHP_EOL);
            $operationResponse = $instanceAdminClient->createInstance(
                $formattedParent,
                $instance_id,
                $instance,
                $clusters
            );
            $operationResponse->pollUntilComplete();
            if (!$operationResponse->operationSucceeded()) {
                throw new Exception('error creating instance', -1);
            }
        }
    }
    // [END bigtable_create_dev_instance]

}

$project_id = (isset($argv[1])) ? $argv[1] : getenv('PROJECT_ID');
$instance_id = (isset($argv[2])) ? $argv[2] : 'quickstart-instance-php';
$cluster_id = (isset($argv[3])) ? $argv[3] :'php-cluster-d';

create_dev_instance($project_id, $instance_id, $cluster_id);