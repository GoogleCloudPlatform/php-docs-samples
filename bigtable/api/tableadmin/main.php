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
 * - Create a Cloud Bigtable cluster.
 *   https://cloud.google.com/bigtable/docs/creating-cluster
 * - Set your Google Application Default Credentials.
 *   https://developers.google.com/identity/protocols/application-default-credentials
 *
 * Operations performed:
 * - Create a Cloud Bigtable table.
 * - List tables for a Cloud Bigtable instance.
 * - Print metadata of the newly created table.
 * - Create Column Families with different GC rules.
 *     - GC Rules like: MaxAge, MaxVersions, Union, Intersection and Nested.
 * - Delete a Bigtable table.
 */


require __DIR__ . '/vendor/autoload.php';


use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;
use Google\Cloud\Bigtable\Admin\V2\Instance;
use Google\Cloud\Bigtable\Admin\V2\Table;
use Google\Cloud\Bigtable\Admin\V2\ColumnFamily;
use Google\Cloud\Bigtable\Admin\V2\GcRule;
use Google\Cloud\Bigtable\Admin\V2\GcRule\Union as GcRuleUnion;
use Google\Cloud\Bigtable\Admin\V2\GcRule\Intersection as GcRuleIntersection;
use Google\Cloud\Bigtable\Admin\V2\ModifyColumnFamiliesRequest\Modification;

use Google\Cloud\Bigtable\Admin\V2\Table\View;
use Google\ApiCore\ApiException;

use Google\Cloud\Bigtable\Admin\V2\StorageType;
use Google\Cloud\Bigtable\Admin\V2\Instance\Type as InstanceType;

use Google\Protobuf\Duration;



function run_table_operations($project_id, $instance_id, $table_id){
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
    $tableAdminClient = new BigtableTableAdminClient();

    $formattedParent = $instanceAdminClient->projectName( $project_id );
    $formattedInstance = $instanceAdminClient->instanceName($project_id, $instance_id);
    $formattedTable = $tableAdminClient->tableName($project_id, $instance_id,$table_id);

    // Check whether table exists in an instance.
    // Create table if it does not exists.
    $table = new Table();
    printf("Checking if table %s exists\n", $table_id);

    try {
        $tableAdminClient->getTable( $table , [ 'view'=> View::NAME_ONLY ] );
        printf("Table %s alredy exists\n", $table_id);
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf("Creating the %s table\n", $table_id);

            $tableAdminClient->createtable(
                $formattedInstance,
                $table_id,
                $table
            );
            printf("Created table %s\n", $table_id);
        }
    }
    // [START bigtable_list_tables]
    $tables = iterator_to_array( $tableAdminClient->listTables($formattedInstance)->iterateAllElements() );
    if($tables != []){
        foreach($tables as $table){
            echo $table->getName()."\n";
        }
    } else {
        printf("No table exists  in current project...\n");
    }
    // [END bigtable_list_tables]

    // [START bigtable_create_family_gc_max_age]
    printf("Creating column family cf1 with MaxAge GC Rule...\n");
    // Create a column family with GC policy : maximum age
    // where age = current time minus cell timestamp
    
    $columnFamily1 = new ColumnFamily();
    $duration = new Duration();
    $duration->setSeconds( 3600 * 24 * 5 );
    $MaxAgeRule = (new GcRule)->setMaxAge( $duration );
    $columnFamily1->setGcRule( $MaxAgeRule );
    
    $columnModification = new Modification();
    $columnModification->setId('cf1');
    $columnModification->setCreate($columnFamily1);
    $tableAdminClient->modifyColumnFamilies($formattedTable , [$columnModification] );
    printf("Created column family cf1 with MaxAge GC Rule.\n");
    
    // [END bigtable_list_tables]

    // [START bigtable_create_family_gc_max_age]
    
    printf("Creating column family cf2 with max versions GC rule...\n");
    $columnFamily2 = new ColumnFamily();
    $maxVersionRule = (new GcRule)->setMaxNumVersions(2);
    $columnFamily2->setGCRule( $maxVersionRule );

    $columnModification = new Modification();
    $columnModification->setId('cf2');
    $columnModification->setCreate($columnFamily2);
    $tableAdminClient->modifyColumnFamilies($formattedTable , [$columnModification] );

    printf("Created column family cf2 with Max Versions GC Rule.\n");
    
    // [END bigtable_create_family_gc_max_versions]

    // [START bigtable_create_family_gc_union]
    
    printf("Creating column family cf3 with union GC rule...\n");
    // Create a column family with GC policy to drop data that matches
    // at least one condition.
    // Define a GC rule to drop cells older than 5 days or not the
    // most recent version
    $columnFamily3 = new ColumnFamily();
    
    $rule_union = new GcRuleUnion();
    $rule_union_array = [
        (new GcRule)->setMaxNumVersions(2),
        (new GcRule)->setMaxAge( (new Duration())->setSeconds( 3600 * 24 * 5 ) )
    ];
    $rule_union->setRules($rule_array);
    $union = new GcRule();
    $union->setUnion($rule_union);
    
    $columnFamily3->setGCRule( $union );
    
    $columnModification = new Modification();
    $columnModification->setId('cf3');
    $columnModification->setCreate($columnFamily3);
    $tableAdminClient->modifyColumnFamilies($formattedTable , [$columnModification] );
    
    print("Created column family cf3 with Union GC rule\n");
    
    // [END bigtable_create_family_gc_union]

    // [START bigtable_create_family_gc_intersection]
    
    printf("Creating column family cf4 with Intersection GC rule...\n");
    $columnFamily4 = new ColumnFamily();

    $intersection_rule = new GcRuleIntersection();
    $intersection_array = [
        (new GcRule)->setMaxAge( (new Duration())->setSeconds( 3600 * 24 * 5 ) ),
        (new GcRule)->setMaxNumVersions(2)
    ];
    $intersection_rule->setRules( $intersection_array );

    $intersection = new GcRule();
    $intersection->setIntersection($intersection_rule);
    
    $columnFamily4->setGCRule( $intersection );

    $columnModification = new Modification();
    $columnModification->setId('cf4');
    $columnModification->setCreate($columnFamily4);
    $tableAdminClient->modifyColumnFamilies($formattedTable , [$columnModification] );

    print("Created column family cf4 with Union GC rule\n");
    
    // [END bigtable_create_family_gc_intersection]

    // [START bigtable_create_family_gc_nested]
    
    printf("Creating column family cf5 with a Nested GC rule...\n");
    // Create a column family with nested GC policies.
    // Create a nested GC rule:
    // Drop cells that are either older than the 10 recent versions
    // OR
    // Drop cells that are older than a month AND older than the
    // 2 recent versions
    $columnFamily5 = new ColumnFamily();
    $rule1 = (new GcRule)->setMaxNumVersions(10);

    $rule2Intersection = new GcRuleIntersection();
    $rule2Duration1 = new Duration();
    $rule2Duration1->setSeconds( 3600 * 24 * 30 );
    $rule2Array = [
        (new GcRule)->setMaxAge( $rule2Duration1 ),
        (new GcRule)->setMaxNumVersions(2)
    ];
    $rule2Intersection->setRules($rule2Array);
    $rule2 = new GcRule();
    $rule2->setIntersection( $rule2Intersection );

    $nested_rule  = new GcRuleUnion();
    $nested_rule->setRules([
        $rule1,
        $rule2
    ]);
    $nested_rule = (new GcRule())->setUnion($nested_rule);

    $columnFamily5->setGCRule( $nested_rule );

    $columnModification = new Modification();
    $columnModification->setId('cf5');
    $columnModification->setCreate($columnFamily5);
    $tableAdminClient->modifyColumnFamilies($formattedTable , [$columnModification] );

    printf("Created column family cf5 with a Nested GC rule.\n");
    
    // [END bigtable_create_family_gc_nested]
    
    // [START bigtable_list_column_families]
    $table = $tableAdminClient->getTable($formattedTable);
    $columnFamilies = iterator_to_array( $table->getColumnFamilies()->getIterator() );
    foreach( $columnFamilies as $k => $columnFamily){
        printf("Column Family: %s\n", $k );
        printf("GC Rule:\n%s\n",$columnFamily->serializeToJsonString());
    }
    // [END bigtable_list_column_families]
    printf("Print column family cf1 GC rule before update...\n");
    printf("Column Family: cf1\n");
    printf("%s\n",$columnFamily1->serializeToJsonString());
    // [START bigtable_update_gc_rule]
    printf("Updating column family cf1 GC rule...\n");
    $columnFamily1->setGcRule( (new GcRule)->setMaxNumVersions(1) );
    // Update the column family cf1 to update the GC rule
    $columnModification = new Modification();
    $columnModification->setId('cf1');
    $columnModification->setUpdate($columnFamily1);
    //$tableAdminClient->modifyColumnFamilies($formattedTable , [$columnModification] );
    printf("Print column family cf1 GC rule after update...\n");
    printf('Column Family: cf1');
    printf("%s\n",$columnFamily1->serializeToJsonString());
    // [END bigtable_update_gc_rule]

    // [START bigtable_delete_family]
    printf("Delete a column family cf2...\n");
    // Delete a column family
    $columnModification = new Modification();
    $columnModification->setId('cf2');
    $columnModification->setDrop(true);
    $tableAdminClient->modifyColumnFamilies($formattedTable , [$columnModification] );
    printf("Column family cf2 deleted successfully.\n");
    // [END bigtable_delete_family]

}
function delete_table($project_id, $instance_id, $table_id){
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
    $tableAdminClient = new BigtableTableAdminClient();
    
    $formattedTable = $tableAdminClient->tableName($project_id, $instance_id,$table_id);

    $table = new Table();

    // [START bigtable_delete_table]
    // Delete the entire table

    printf("Checking if table %s exists...\n",$table_id);

    try {
        printf("Table %s exists.\n", $table_id);
        printf("Deleting %s table.\n", $table_id);
        $tableAdminClient->deleteTable($formattedTable);
        printf("Deleted %s table.\n", $table_id);
    } catch (ApiException $e) {
        if ($e->getStatus() === 'NOT_FOUND') {
            printf("Table %s does not exists\n", $table_id);
        }
    }
    // [END bigtable_delete_table]
}
if(basename(__FILE__) == $_SERVER['SCRIPT_FILENAME']){
    run_table_operations(getenv('PROJECT_ID') , 'quickstart-instance-php', 'quickstart-instance-table');
    delete_table(getenv('PROJECT_ID') , 'quickstart-instance-php', 'quickstart-instance-table');
}
