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
require __DIR__ . '/vendor/autoload.php';
use Google\Cloud\Bigtable\DataClient;
use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;

$instance_id = 'php-instance-test'; # instance-id
$table_id    = 'bigtable-php-table'; # my-table
$project_id  = getenv('PROJECT_ID');


// [START bigtable_quickstart]
// Connect to an existing table with an existing instance.
$dataClient = new DataClient(
	$instance_id,
	$table_id
);
$key = 'rk1';
// Read a row from my-table using a row key
$row = $dataClient->readRow($key);

$column_family_id = 'cf1';
$column_id = 'cq1';
// Get the Value from the Row, using the column_family_id and column_id

$value = $row[$column_family_id][$column_id][0]['value'];

printf("Row key: %s\nData: %s\n",$key,$value);
// [END bigtable_quickstart]