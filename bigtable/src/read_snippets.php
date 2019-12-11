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

if (count($argv) !== 5) {
    return printf("Usage: php %s PROJECT_ID INSTANCE_ID TABLE_ID READ_TYPE" . PHP_EOL, __FILE__);
}
list($_, $project_id, $instance_id, $table_id, $read_type) = $argv;

$validReadTypes = ['read_row', 'read_rows', 'read_row_range', 'read_row_ranges',
    'read_prefix', 'read_filter', 'read_row_partial'];
if (!in_array($read_type, $validReadTypes)) {
    throw new Exception(sprintf(
        'Invalid READ_TYPE %s, must be one of: %s',
        $read_type,
        implode(', ', $validReadTypes)
    ));
}

// [START bigtable_reads_row]
// [START bigtable_reads_row_partial]
// [START bigtable_reads_rows]
// [START bigtable_reads_row_range]
// [START bigtable_reads_row_ranges]
// [START bigtable_reads_prefix]
// [START bigtable_reads_filter]

use Google\Cloud\Bigtable\BigtableClient;
use Google\Cloud\Bigtable\Filter;

/** Uncomment and populate these variables in your code */
// $project_id = 'The Google project ID';
// $instance_id = 'The Bigtable instance ID';
// $table_id = 'mobile-time-series';

// Connect to an existing table with an existing instance.
$dataClient = new BigtableClient([
    'projectId' => $project_id,
]);
$table = $dataClient->table($instance_id, $table_id);

// Helper function for printing the row data
function print_row($key, $row)
{
    printf('Reading data for row %s' . PHP_EOL, $key);
    foreach ((array)$row as $family => $cols) {
        printf('Column Family %s' . PHP_EOL, $family);
        foreach ($cols as $col => $data) {
            for ($i = 0; $i < count($data); $i++) {
                printf(
                    "\t%s: %s @%s%s" . PHP_EOL,
                    $col,
                    $data[$i]['value'],
                    $data[$i]['timeStamp'],
                    $data[$i]['labels'] ? sprintf(" [%s]", $data[$i]['labels']) : ''
                );
            }
        }
    }
    print(PHP_EOL);
}

// [END bigtable_reads_row]
// [END bigtable_reads_row_partial]
// [END bigtable_reads_rows]
// [END bigtable_reads_row_range]
// [END bigtable_reads_row_ranges]
// [END bigtable_reads_prefix]
// [END bigtable_reads_filter]

function read_row($table)
{
    // [START bigtable_reads_row]
    $rowkey = "phone#4c410523#20190501";
    $row = $table->readRow($rowkey);

    print_row($rowkey, $row);
    // [END bigtable_reads_row]
}

function read_row_partial($table)
{
    // [START bigtable_reads_row_partial]
    $rowkey = "phone#4c410523#20190501";
    $rowFilter = Filter::qualifier()->exactMatch("os_build");
    $row = $table->readRow($rowkey, ['filter' => $rowFilter]);

    print_row($rowkey, $row);
    // [END bigtable_reads_row_partial]
}

function read_rows($table)
{
    // [START bigtable_reads_rows]
    $rows = $table->readRows(
        ["rowKeys" => ["phone#4c410523#20190501", "phone#4c410523#20190502"]]
    );

    foreach ($rows as $key => $row) {
        print_row($key, $row);
    }
    // [END bigtable_reads_rows]
}

function read_row_range($table)
{
    // [START bigtable_reads_row_range]
    $rows = $table->readRows([
        'rowRanges' => [
            [
                'startKeyClosed' => 'phone#4c410523#20190501',
                'endKeyOpen' => 'phone#4c410523#201906201'
            ]
        ]
    ]);

    foreach ($rows as $key => $row) {
        print_row($key, $row);
    }
    // [END bigtable_reads_row_range]
}

function read_row_ranges($table)
{
    // [START bigtable_reads_row_ranges]
    $rows = $table->readRows([
        'rowRanges' => [
            [
                'startKeyClosed' => 'phone#4c410523#20190501',
                'endKeyOpen' => 'phone#4c410523#201906201'
            ],
            [
                'startKeyClosed' => 'phone#5c10102#20190501',
                'endKeyOpen' => 'phone#5c10102#201906201'
            ]
        ]
    ]);

    foreach ($rows as $key => $row) {
        print_row($key, $row);
    }
    // [END bigtable_reads_row_ranges]
}

function read_prefix($table)
{
    // [START bigtable_reads_prefix]
    $prefix = 'phone#';
    $end = $prefix;
    $end[-1] = chr(
        ord($end[-1]) + 1
    );

    $rows = $table->readRows([
        'rowRanges' => [
            [
                'startKeyClosed' => $prefix,
                'endKeyClosed' => $end,
            ]
        ]
    ]);

    foreach ($rows as $key => $row) {
        print_row($key, $row);
    }
    // [END bigtable_reads_prefix]
}

function read_filter($table)
{
    // [START bigtable_reads_filter]
    $rowFilter = Filter::value()->regex('PQ2A.*$');

    $rows = $table->readRows([
        'filter' => $rowFilter
    ]);

    foreach ($rows as $key => $row) {
        print_row($key, $row);
    }
    // [END bigtable_reads_filter]
}

// Call the function for the supplied READ_TYPE
call_user_func($read_type, $table);
