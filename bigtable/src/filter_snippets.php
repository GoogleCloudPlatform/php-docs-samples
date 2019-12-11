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
    return printf("Usage: php %s PROJECT_ID INSTANCE_ID TABLE_ID FILTER_TYPE" . PHP_EOL, __FILE__);
}
list($_, $project_id, $instance_id, $table_id, $filter_type) = $argv;

$validFilterTypes = [
    'filter_limit_row_sample',
    'filter_limit_row_regex',
    'filter_limit_cells_per_col',
    'filter_limit_cells_per_row',
    'filter_limit_cells_per_row_offset',
    'filter_limit_col_family_regex',
    'filter_limit_col_qualifier_regex',
    'filter_limit_col_range',
    'filter_limit_value_range',
    'filter_limit_value_regex',
    'filter_limit_timestamp_range',
    'filter_limit_block_all',
    'filter_limit_pass_all',
    'filter_modify_strip_value',
    'filter_modify_apply_label',
    'filter_composing_chain',
    'filter_composing_interleave',
    'filter_composing_condition'
];
if (!in_array($filter_type, $validFilterTypes)) {
    throw new Exception(sprintf(
        'Invalid FILTER_TYPE %s, must be one of: %s',
        $filter_type,
        implode(', ', $validFilterTypes)
    ));
}

// [START bigtable_filters_limit_row_sample]
// [START bigtable_filters_limit_row_regex]
// [START bigtable_filters_limit_cells_per_col]
// [START bigtable_filters_limit_cells_per_row]
// [START bigtable_filters_limit_cells_per_row_offset]
// [START bigtable_filters_limit_col_family_regex]
// [START bigtable_filters_limit_col_qualifier_regex]
// [START bigtable_filters_limit_col_range]
// [START bigtable_filters_limit_value_range]
// [START bigtable_filters_limit_value_regex]
// [START bigtable_filters_limit_timestamp_range]
// [START bigtable_filters_limit_block_all]
// [START bigtable_filters_limit_pass_all]
// [START bigtable_filters_modify_strip_value]
// [START bigtable_filters_modify_apply_label]
// [START bigtable_filters_composing_chain]
// [START bigtable_filters_composing_interleave]
// [START bigtable_filters_composing_condition]

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

function read_filter($table, $filter)
{
    $rows = $table->readRows([
        'filter' => $filter
    ]);

    foreach ($rows as $key => $row) {
        print_row($key, $row);
    }
}

// [END bigtable_filters_limit_row_sample]
// [END bigtable_filters_limit_row_regex]
// [END bigtable_filters_limit_cells_per_col]
// [END bigtable_filters_limit_cells_per_row]
// [END bigtable_filters_limit_cells_per_row_offset]
// [END bigtable_filters_limit_col_family_regex]
// [END bigtable_filters_limit_col_qualifier_regex]
// [END bigtable_filters_limit_col_range]
// [END bigtable_filters_limit_value_range]
// [END bigtable_filters_limit_value_regex]
// [END bigtable_filters_limit_timestamp_range]
// [END bigtable_filters_limit_block_all]
// [END bigtable_filters_limit_pass_all]
// [END bigtable_filters_modify_strip_value]
// [END bigtable_filters_modify_apply_label]
// [END bigtable_filters_composing_chain]
// [END bigtable_filters_composing_interleave]
// [END bigtable_filters_composing_condition]


function filter_limit_row_sample($table)
{
    // [START bigtable_filters_limit_row_sample]
    $filter = Filter::key()->sample(.75);
    read_filter($table, $filter);
    // [END bigtable_filters_limit_row_sample]
}

function filter_limit_row_regex($table)
{
    // [START bigtable_filters_limit_row_regex]
    $filter = Filter::key()->regex(".*#20190501$");
    read_filter($table, $filter);
    // [END bigtable_filters_limit_row_regex]
}

function filter_limit_cells_per_col($table)
{
    // [START bigtable_filters_limit_cells_per_col]
    $filter = Filter::limit()->cellsPerColumn(2);
    read_filter($table, $filter);
    // [END bigtable_filters_limit_cells_per_col]
}

function filter_limit_cells_per_row($table)
{
    // [START bigtable_filters_limit_cells_per_row]
    $filter = Filter::limit()->cellsPerRow(2);
    read_filter($table, $filter);
    // [END bigtable_filters_limit_cells_per_row]
}

function filter_limit_cells_per_row_offset($table)
{
    // [START bigtable_filters_limit_cells_per_row_offset]
    $filter = Filter::offset()->cellsPerRow(2);
    read_filter($table, $filter);
    // [END bigtable_filters_limit_cells_per_row_offset]
}

function filter_limit_col_family_regex($table)
{
    // [START bigtable_filters_limit_col_family_regex]
    $filter = Filter::family()->regex("stats_.*$");
    read_filter($table, $filter);
    // [END bigtable_filters_limit_col_family_regex]
}

function filter_limit_col_qualifier_regex($table)
{
    // [START bigtable_filters_limit_col_qualifier_regex]
    $filter = Filter::qualifier()->regex("connected_.*$");
    read_filter($table, $filter);
    // [END bigtable_filters_limit_col_qualifier_regex]
}

function filter_limit_col_range($table)
{
    // [START bigtable_filters_limit_col_range]
    $filter = Filter::qualifier()
        ->rangeWithinFamily("cell_plan")
        ->startClosed("data_plan_01gb")
        ->endOpen("data_plan_10gb");
    read_filter($table, $filter);
    // [END bigtable_filters_limit_col_range]
}

function filter_limit_value_range($table)
{
    // [START bigtable_filters_limit_value_range]
    $filter = Filter::value()
        ->range()
        ->startClosed("PQ2A.190405")
        ->endOpen("PQ2A.190406");
    read_filter($table, $filter);
    // [END bigtable_filters_limit_value_range]
}

function filter_limit_value_regex($table)
{
    // [START bigtable_filters_limit_value_regex]
    $filter = Filter::value()->regex("PQ2A.*$");
    read_filter($table, $filter);
    // [END bigtable_filters_limit_value_regex]
}

function filter_limit_timestamp_range($table)
{
    // [START bigtable_filters_limit_timestamp_range]
    $start = 0;
    $end = (time() - 60 * 60) * 1000 * 1000;
    $filter = Filter::timestamp()
        ->range()
        ->startClosed($start)
        ->endOpen($end);
    read_filter($table, $filter);
    // [END bigtable_filters_limit_timestamp_range]
}

function filter_limit_block_all($table)
{
    // [START bigtable_filters_limit_block_all]
    $filter = Filter::block();
    read_filter($table, $filter);
    // [END bigtable_filters_limit_block_all]
}

function filter_limit_pass_all($table)
{
    // [START bigtable_filters_limit_pass_all]
    $filter = Filter::pass();
    read_filter($table, $filter);
    // [END bigtable_filters_limit_pass_all]
}

function filter_modify_strip_value($table)
{
    // [START bigtable_filters_modify_strip_value]
    $filter = Filter::value()->strip();
    read_filter($table, $filter);
    // [END bigtable_filters_modify_strip_value]
}

function filter_modify_apply_label($table)
{
    // [START bigtable_filters_modify_apply_label]
    $filter = Filter::label("labelled");
    read_filter($table, $filter);
    // [END bigtable_filters_modify_apply_label]
}

function filter_composing_chain($table)
{
    // [START bigtable_filters_composing_chain]
    $filter = Filter::chain()
        ->addFilter(Filter::limit()->cellsPerColumn(1))
        ->addFilter(Filter::family()->exactMatch("cell_plan"));
    read_filter($table, $filter);
    // [END bigtable_filters_composing_chain]
}

function filter_composing_interleave($table)
{
    // [START bigtable_filters_composing_interleave]
    $filter = Filter::interleave()
        ->addFilter(Filter::value()->exactMatch(unpack('C*', 1)))
        ->addFilter(Filter::qualifier()->exactMatch("os_build"));
    read_filter($table, $filter);
    // [END bigtable_filters_composing_interleave]
}

function filter_composing_condition($table)
{
    // [START bigtable_filters_composing_condition]
    $filter = Filter::condition(
        Filter::chain()
            ->addFilter(Filter::value()->exactMatch(unpack('C*', 1)))
            ->addFilter(Filter::qualifier()->exactMatch("data_plan_10gb"))
    )
        ->then(Filter::label("passed-filter"))
        ->otherwise(Filter::label("filtered-out"));
    read_filter($table, $filter);
    // [END bigtable_filters_composing_condition]
}


// Call the function for the supplied READ_TYPE
call_user_func($filter_type, $table);
