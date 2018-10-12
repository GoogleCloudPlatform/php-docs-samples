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
 */
require '../vendor/autoload.php';


use Google\Cloud\Bigtable\DataClient;
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;
use Google\Cloud\Bigtable\Admin\V2\ColumnFamily;
use Google\Cloud\Bigtable\Admin\V2\Instance;
use Google\Cloud\Bigtable\Admin\V2\Table;
use Google\Cloud\Bigtable\RowMutation;
use Google\Cloud\Bigtable\Admin\V2\GcRule;
use Google\Cloud\Bigtable\V2\RowFilter;


$project_id  = getenv('PROJECT_ID');
$instance_id = 'php-instance-test';
$table_id    = 'bigtable-php-table';
$location_id = 'us-east1-b';


// [START connecting_to_bigtable]
// Create Instance Admin Client and Table Admin Client
$instanceAdminClient = new BigtableInstanceAdminClient();
$tableAdminClient = new BigtableTableAdminClient();
// Create the DataClient
$dataClient = new DataClient( $instance_id, $table_id);
// [END connecting_to_bigtable]

// [START creating_a_table]
$formattedParent = $tableAdminClient->instanceName(
    $project_id,
    $instance_id
);
$columnFamily = new ColumnFamily();
$columnRule = (new GcRule)->setMaxNumVersions(2);
$columnFamily->setGcRule($columnRule);
$columnFamilyId = 'cf1';

$table = new Table([
    'column_families' => [
        $columnFamilyId => $columnFamily
    ]
]);

$tableName = $tableAdminClient->tableName( $project_id, $instance_id, $table_id );
if(!$table->exists( $tableName )){
    $tableAdminClient->createTable(
        $formattedParent,
        $table_id,
        $table
    );
}
// [END creating_a_table]

// [START writing_rows]

echo "Writing some greetings to the table.\n";

$greetings = ['Hello World!', 'Hello Cloud Bigtable!', 'Hello PHP!'];

$entries = [];
$column = 'greeting';

foreach($greetings as $i=>$value){
    $row_key = sprintf('greeting%s',$i);

    $rowMutation = new RowMutation( $row_key );
    $rowMutation->upsert($columnFamilyId, $column, $value, time()*1000 );
    $entries[] = $rowMutation;
}
$dataClient->mutateRows( $entries );
// [END writing_rows]

// [START getting_a_row]
echo "Getting a single greeting by row key.\n";

$key = 'greeting0';

// Only retrieve the most recent version of the cell.
$row_filter = (new RowFilter)->setCellsPerColumnLimitFilter(1);

$row = $dataClient->readRow($key, [
    'rowFilter' => $row_filter
]);
echo $row[$columnFamilyId][$column][0]['value']."\n";

// [END getting_a_row]

// [START scanning_all_rows]
echo 'Scanning for all greetings:'."\n";

$partial_rows = $dataClient->readRows(
    [
        'rowFilter' => $row_filter
    ]
)->readAll();

foreach($partial_rows as $row){
    echo $row[$columnFamilyId][$column][0]['value']."\n";
}
// [END scanning_all_rows]

// [START deleting_a_table]

echo sprintf('Deleting the %s table.',$table_id)."\n";

$formattedName = $tableAdminClient->tableName(
    $project_id,
    $instance_id,
    $table_id
);

$tableAdminClient->deleteTable($formattedName);
// [END deleting_a_table]
