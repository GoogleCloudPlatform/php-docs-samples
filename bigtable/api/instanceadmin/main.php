<?php

/**
 * Copyright 2018 Google Inc.
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


require __DIR__ . '/vendor/autoload.php';


use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\Instance;
use Google\Cloud\Bigtable\Admin\V2\Cluster;
use Google\Cloud\Bigtable\Admin\V2\StorageType;
use Google\Cloud\Bigtable\Admin\V2\Instance\Type as InstanceType;


function run_instance_operations($project_id, $instance_id){
    /**
     * Check Instance exists.
     * * Creates a Production instance with default Cluster.
     * * List instances in a project.
     *   List clusters in an instance.
     * 
     * @param string $project_id Project id of the client.
     * @param string $instance_id Instance id of the client.
     */
   
    $instanceAdminClient = new BigtableInstanceAdminClient();

    $cluster_id  = 'php-cluster';
    $table_id    = 'bigtable-php-table';
    $location_id = 'us-east1-b';
    $formattedInstance = $instanceAdminClient->instanceName($project_id, $instance_id);
    $formattedParent = $instanceAdminClient->projectName( $project_id );

    $instance = new Instance();
    $instance->setDisplayName( $instance_id );
    $instance->setName( $formattedInstance );
    
    $serve_nodes = 3;
    $storage_type = StorageType::SSD;
    $production = InstanceType::PRODUCTION;
    $labels = [ 'prod-label' => 'prod-label' ];

    $formattedParent = $instanceAdminClient->projectName( $project_id );
    $instance = new Instance();
    $instance->setDisplayName( $instance_id );

    $instance->setLabels( $labels );
    $instance->setType( $production );

    // [START bigtable_check_instance_exists]
    try {
        $instanceAdminClient->getInstance( $formattedInstance );
        printf("Instance %s already exists.\n", $instance_id);
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf("Instance %s does not exists.\n", $instance_id);
        }
    }
    // [END bigtable_check_instance_exists]
    
    // [START bigtable_create_prod_instance]
    $cluster = new Cluster();
    $cluster->setDefaultStorageType( $storage_type );
    $cluster->setLocation(
        $instanceAdminClient->locationName(
            $project_id,
            $location_id
        )
    );
    $cluster->setServeNodes( $serve_nodes );
    $clusters = [
        $cluster_id => $cluster
    ];
    try {
        $instanceAdminClient->getInstance( $formattedInstance );
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf("Creating an Instance:\n");
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
    // [END bigtable_create_prod_instance]

    // [START bigtable_list_instances]
    printf("Listing Instances:\n");
    $instances_local = $instanceAdminClient->listInstances($formattedParent)->getInstances();
    $instance_array = iterator_to_array( $instances_local->getIterator() );
    foreach( $instance_array as $instance ){
        echo $instance->getDisplayName()."\n";
    }
    // [END bigtable_list_instances]
    // [START bigtable_get_instance]
    $labels = json_encode( iterator_to_array( $instance->getLabels()->getIterator() ) );
    printf("Name of instance: %s\nLabels: %s\n", $instance->getDisplayName(), $labels );
    // [END bigtable_get_instance]
    // [START bigtable_get_clusters]
    printf("Listing Clusters...\n");
    $clusters_local = $instanceAdminClient->listClusters($formattedInstance)->getClusters();
    $clusters_array = iterator_to_array( $clusters_local->getIterator() );
    
    foreach($clusters_array as $cluster){
        echo $cluster->getName()."\n";
    }
    // [END bigtable_get_clusters]
}


function create_dev_instance($project_id, $instance_id, $cluster_id){
    /**
     * Creates a Development instnace with the name "hdd-instance"
     * * location us-central1-f
     * * Cluster nodes should not be set while creating Develpment
     * * Instance
     * 
     * @param string $project_id Project id of the client.
     * @param string $instance_id Instance id of the client.
     */
    
    $instanceAdminClient = new BigtableInstanceAdminClient();

    $formattedInstance = $instanceAdminClient->instanceName($project_id, $instance_id);
    $formattedParent = $instanceAdminClient->projectName( $project_id );
    // [START bigtable_create_dev_instance]
    printf("Creating a DEVELOPMENT Instance\n");
    // Set options to create an Instance
    $table_id    = 'bigtable-php-table';
    $location_id = 'us-east1-b';
    $serve_nodes = 3;
    $storage_type = StorageType::HDD;
    $development = InstanceType::DEVELOPMENT;
    $labels = [ 'dev-label' => 'dev-label' ];

    $instance = new Instance();
    $instance->setDisplayName( $instance_id );
    $instance->setName( $formattedInstance );

    $formattedParent = $instanceAdminClient->projectName( $project_id );
    
    # Create instance with given options
    $instance = new Instance();
    $instance->setDisplayName( $instance_id );
    $instance->setLabels( $labels );
    $instance->setType( $development );

    try {
        $instanceAdminClient->getInstance( $formattedInstance );
        printf("Instance %s already exists.\n", $instance_id);
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf("Instance %s does not exists.\n", $instance_id);
        }
    }

    // Create cluster with given options
    $cluster = new Cluster();
    $cluster->setDefaultStorageType( $storage_type );
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
        $instanceAdminClient->getInstance( $formattedInstance );
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf("Creating an Instance\n");
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


function delete_instance($project_id, $instance_id){
    /**
     * Delete the Instance
     *
     * @param string $project_id Project id of the client.
     * @param string $instance_id Instance id of the client.
     */
    
    $instanceAdminClient = new BigtableInstanceAdminClient();

    $formattedParent = $instanceAdminClient->projectName( $project_id );
    $formattedInstance = $instanceAdminClient->instanceName($project_id, $instance_id);

    $instance = new Instance();

    // [START bigtable_delete_instance]
    printf("Deleting Instance\n");
    try {
        $instanceAdminClient->deleteInstance( $formattedInstance );
        printf("Deleted Instance: %s.\n", $instance_id);
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf("Instance %s does not exists.\n", $instance_id);
        }
    }
    // [END bigtable_delete_instance]
}


function add_cluster($project_id, $instance_id, $cluster_id){
    /**
     * Add Cluster
     *
     * @param string $project_id Project id of the client.
     * @param string $instance_id Instance id of the client.
     * @param string cluster_id Cluster id.
     */
    
    $instanceAdminClient = new BigtableInstanceAdminClient();

    $formattedInstance = $instanceAdminClient->instanceName($project_id, $instance_id);
    $formattedCluster = $instanceAdminClient->clusterName($project_id, $instance_id,$cluster_id);

    $instance = new Instance();
    $instance_exists = true;
    try {
        $instanceAdminClient->getInstance( $formattedInstance );
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            $instance_exists = false;
        }
    }
    if(!$instance_exists){
        printf("Instance %s does not exists.\n", $instance_id);
    }else{
        printf("Adding Cluster to Instance %s\n", $instance_id);
        // [START bigtable_create_cluster]
        printf("Listing Clusters...\n");

        $clusters_local = $instanceAdminClient->listClusters($formattedInstance)->getClusters();
        $clusters_array = iterator_to_array( $clusters_local->getIterator() );

        $table_id    = 'bigtable-php-table';
        $location_id = 'us-east1-b';
        $storage_type = StorageType::SSD;
        $serve_nodes = 3;

        foreach($clusters_array as $cluster){
            echo $cluster->getName()."\n";
        }
        $cluster = new Cluster();
        $cluster->setServeNodes( $serve_nodes );
        $cluster->setDefaultStorageType( $storage_type );
        $cluster->setLocation(
            $instanceAdminClient->locationName(
                $project_id,
                $location_id
            )
        );
        try {
            $instanceAdminClient->getCluster( $formattedCluster );
            printf("Cluster not created, as %s", $cluster_id);
        } catch (ApiException $e) {
            if ($e->getStatus() === 'NOT_FOUND') {
                $operationResponse = $instanceAdminClient->createCluster($formattedInstance, $cluster_id, $cluster);

                $operationResponse->pollUntilComplete();
                if ($operationResponse->operationSucceeded()) {
                    $result = $operationResponse->getResult();
                    printf("Cluster created: %s", $cluster_id);
                } else {
                    $error = $operationResponse->getError();
                    printf("Cluster not created: %s", $error);
                }
            }
        }
        // [END bigtable_create_cluster]
    }
}


function delete_cluster($project_id, $instance_id, $cluster_id){
    /**
     * Delete the cluster
     *
     * @param string $project_id Project id of the client.
     * @param string $instance_id Instance id of the client.
     * @param string cluster_id Cluster id.
     */
    
    $instanceAdminClient = new BigtableInstanceAdminClient();

    $formattedInstance = $instanceAdminClient->instanceName($project_id, $instance_id);
    $formattedCluster = $instanceAdminClient->clusterName($project_id, $instance_id,$cluster_id);
    $cluster = new Cluster();
    // [START bigtable_delete_cluster]
    printf("Deleting Cluster\n");
    try {
        $instanceAdminClient->deleteCluster($formattedCluster);
        printf("Cluster {} does not exist.\n",$cluster_id);
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf("Cluster deleted: %s\n",$cluster_id);
        }
    }
    // [END bigtable_delete_cluster]
}


if(basename(__FILE__) == $_SERVER['SCRIPT_FILENAME']){
    $instance = 'quickstart-instance-php2';
    $cluster = 'php-cluster-d2';
    run_instance_operations( getenv('PROJECT_ID') , $instance );
    delete_instance( getenv('PROJECT_ID') , $instance );
    create_dev_instance( getenv('PROJECT_ID') , $instance , $cluster );    
    add_cluster( getenv('PROJECT_ID') , $instance , $cluster );
    delete_cluster( getenv('PROJECT_ID') , $instance , $cluster );
    delete_instance( getenv('PROJECT_ID') , $instance );
}
