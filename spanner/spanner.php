<?php
/**
 * Copyright 2016 Google LLC
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

namespace Google\Cloud\Samples\Spanner;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;

# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

$application = new Application('Cloud Spanner');

$inputDefinition = new InputDefinition([
    new InputArgument('instance_id', InputArgument::REQUIRED, 'The instance id'),
    new InputArgument('database_id', InputArgument::REQUIRED, 'The database id'),
]);

$instanceInputDefinition = new InputDefinition([
    new InputArgument('instance_id', InputArgument::REQUIRED, 'The instance id'),
]);

$idbInputDefinition = new InputDefinition([
    new InputArgument('instance_id', InputArgument::REQUIRED, 'The instance id'),
    new InputArgument('database_id', InputArgument::REQUIRED, 'The database id'),
    new InputArgument('backup_id', InputArgument::REQUIRED, 'The backup id'),
]);

$ibInputDefinition = new InputDefinition([
    new InputArgument('instance_id', InputArgument::REQUIRED, 'The instance id'),
    new InputArgument('backup_id', InputArgument::REQUIRED, 'The backup id'),
]);

// Create Database command
$application->add((new Command('create-database'))
    ->setDefinition($inputDefinition)
    ->setDescription('Creates a database and tables for sample data.')
    ->setCode(function ($input, $output) {
        create_database(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Insert data command
$application->add((new Command('insert-data'))
    ->setDefinition($inputDefinition)
    ->setDescription('Inserts sample data into the given database.')
    ->setCode(function ($input, $output) {
        insert_data(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Query data command
$application->add((new Command('query-data'))
    ->setDefinition($inputDefinition)
    ->setDescription('Queries sample data from the database using SQL.')
    ->setCode(function ($input, $output) {
        query_data(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Read data command
$application->add((new Command('read-data'))
    ->setDefinition($inputDefinition)
    ->setDescription('Reads sample data from the database.')
    ->setCode(function ($input, $output) {
        read_data(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Read stale data command
$application->add((new Command('read-stale-data'))
    ->setDefinition($inputDefinition)
    ->setDescription('Reads sample data from the database, with a maximum staleness of 3 seconds.')
    ->setCode(function ($input, $output) {
        read_stale_data(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);
// Add column command
$application->add((new Command('add-column'))
    ->setDefinition($inputDefinition)
    ->setDescription('Adds a new column to the Albums table in the example database.')
    ->setCode(function ($input, $output) {
        add_column(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Update data command
$application->add((new Command('update-data'))
    ->setDefinition($inputDefinition)
    ->setDescription('Updates sample data in the database.')
    ->setCode(function ($input, $output) {
        update_data(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Query data with new column command
$application->add((new Command('query-data-with-new-column'))
    ->setDefinition($inputDefinition)
    ->setDescription('Queries sample data from the database using SQL.')
    ->setCode(function ($input, $output) {
        query_data_with_new_column(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Read-write transaction command
$application->add((new Command('read-write-transaction'))
    ->setDefinition($inputDefinition)
    ->setDescription('Performs a read-write transaction to update two sample records in the database.')
    ->setCode(function ($input, $output) {
        read_write_transaction(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Create index command
$application->add((new Command('create-index'))
    ->setDefinition($inputDefinition)
    ->setDescription('Adds a simple index to the example database.')
    ->setCode(function ($input, $output) {
        create_index(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Query data with index command
$application->add((new Command('query-data-with-index'))
    ->setDefinition(clone $inputDefinition)
    ->addOption('start_title', null, InputOption::VALUE_REQUIRED, 'The start of the title index.', 'Aardvark')
    ->addOption('end_title', null, InputOption::VALUE_REQUIRED, 'The end of the title index.', 'Goo')
    ->setDescription('Queries sample data from the database using SQL and an index.')
    ->setCode(function ($input, $output) {
        query_data_with_index(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id'),
            $input->getOption('start_title'),
            $input->getOption('end_title')
        );
    })
);

// Read data with index command
$application->add((new Command('read-data-with-index'))
    ->setDefinition($inputDefinition)
    ->setDescription('Reads sample data from the database using an index.')
    ->setCode(function ($input, $output) {
        read_data_with_index(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Create storing index command
$application->add((new Command('create-storing-index'))
    ->setDefinition($inputDefinition)
    ->setDescription('Adds an storing index to the example database.')
    ->setCode(function ($input, $output) {
        create_storing_index(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Read data with storing index command
$application->add((new Command('read-data-with-storing-index'))
    ->setDefinition($inputDefinition)
    ->setDescription('Reads sample data from the database using an index with a storing clause.')
    ->setCode(function ($input, $output) {
        read_data_with_storing_index(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Read-only transaction command
$application->add((new Command('read-only-transaction'))
    ->setDefinition($inputDefinition)
    ->setDescription('Reads data inside of a read-only transaction.')
    ->setCode(function ($input, $output) {
        read_only_transaction(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Batch query data command
$application->add((new Command('batch-query-data'))
    ->setDefinition($inputDefinition)
    ->setDescription('Batch queries sample data from the database using SQL.')
    ->setCode(function ($input, $output) {
        batch_query_data(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Create table with timestamp column command
$application->add((new Command('create-table-timestamp'))
    ->setDefinition($inputDefinition)
    ->setDescription('Creates a table with a commit timestamp column.')
    ->setCode(function ($input, $output) {
        create_table_with_timestamp_column(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Insert data with timestamp column command
$application->add((new Command('insert-data-timestamp'))
    ->setDefinition($inputDefinition)
    ->setDescription('Inserts data into a table with a commit timestamp column.')
    ->setCode(function ($input, $output) {
        insert_data_with_timestamp_column(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Add timestamp column command
$application->add((new Command('add-timestamp-column'))
    ->setDefinition($inputDefinition)
    ->setDescription('Adds a commit timestamp column to a table.')
    ->setCode(function ($input, $output) {
        add_timestamp_column(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Update data with timestamp column command
$application->add((new Command('update-data-timestamp'))
    ->setDefinition($inputDefinition)
    ->setDescription('Updates sample data in a table with a commit timestamp column.')
    ->setCode(function ($input, $output) {
        update_data_with_timestamp_column(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Query data with timestamp column command
$application->add((new Command('query-data-timestamp'))
    ->setDefinition($inputDefinition)
    ->setDescription('Queries sample data from a database with a commit timestamp column.')
    ->setCode(function ($input, $output) {
        query_data_with_timestamp_column(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Insert struct data command
$application->add((new Command('insert-struct-data'))
    ->setDefinition($inputDefinition)
    ->setDescription('Inserts sample data that can be used to test STRUCT parameters in queries.')
    ->setCode(function ($input, $output) {
        insert_struct_data(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Query data with struct command
$application->add((new Command('query-data-with-struct'))
    ->setDefinition($inputDefinition)
    ->setDescription('Queries sample data from the database with a struct.')
    ->setCode(function ($input, $output) {
        query_data_with_struct(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Query data with array of struct command
$application->add((new Command('query-data-with-array-of-struct'))
    ->setDefinition($inputDefinition)
    ->setDescription('Queries sample data from the database with an array of struct.')
    ->setCode(function ($input, $output) {
        query_data_with_array_of_struct(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Query data with struct field
$application->add((new Command('query-data-with-struct-field'))
    ->setDefinition($inputDefinition)
    ->setDescription('Queries sample data from the database with a struct field value.')
    ->setCode(function ($input, $output) {
        query_data_with_struct_field(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Query data with nested struct field
$application->add((new Command('query-data-with-nested-struct-field'))
    ->setDefinition($inputDefinition)
    ->setDescription('Queries sample data from the database with a nested struct field value.')
    ->setCode(function ($input, $output) {
        query_data_with_nested_struct_field(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Insert data with DML
$application->add((new Command('insert-data-with-dml'))
    ->setDefinition($inputDefinition)
    ->setDescription('Inserts sample data into the given database with a DML statement.')
    ->setCode(function ($input, $output) {
        insert_data_with_dml(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Update data with DML
$application->add((new Command('update-data-with-dml'))
    ->setDefinition($inputDefinition)
    ->setDescription('Updates sample data into the given database with a DML statement.')
    ->setCode(function ($input, $output) {
        update_data_with_dml(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Delete data with DML
$application->add((new Command('delete-data-with-dml'))
    ->setDefinition($inputDefinition)
    ->setDescription('Remove sample data from the given database with a DML statement.')
    ->setCode(function ($input, $output) {
        delete_data_with_dml(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Update data with DML Timestamp
$application->add((new Command('update-data-with-dml-timestamp'))
    ->setDefinition($inputDefinition)
    ->setDescription('Update sample data from the given database with a DML statement and timestamp.')
    ->setCode(function ($input, $output) {
        update_data_with_dml_timestamp(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Write Read with DML
$application->add((new Command('write-read-with-dml'))
    ->setDefinition($inputDefinition)
    ->setDescription('Writes then reads data inside a Transaction with a DML statement.')
    ->setCode(function ($input, $output) {
        write_read_with_dml(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Update Data with DML Structs
$application->add((new Command('update-data-with-dml-structs'))
    ->setDefinition($inputDefinition)
    ->setDescription('Updates data using DML statement with structs.')
    ->setCode(function ($input, $output) {
        update_data_with_dml_structs(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Write Data with DML
$application->add((new Command('write-data-with-dml'))
    ->setDefinition($inputDefinition)
    ->setDescription('Writes sample data into the given database with a DML statement.')
    ->setCode(function ($input, $output) {
        write_data_with_dml(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Query Data with Parameter
$application->add((new Command('query-data-with-parameter'))
    ->setDefinition($inputDefinition)
    ->setDescription('Query DML inserted sample data using SQL with a parameter.')
    ->setCode(function ($input, $output) {
        query_data_with_parameter(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Write Data with DML Transaction
$application->add((new Command('write-data-with-dml-transaction'))
    ->setDefinition($inputDefinition)
    ->setDescription('Performs a read-write transaction to update two sample records in the database.')
    ->setCode(function ($input, $output) {
        write_data_with_dml_transaction(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Update Data with Partitioned DML
$application->add((new Command('update-data-with-partitioned-dml'))
    ->setDefinition($inputDefinition)
    ->setDescription('Updates sample data in the database by partition with a DML statement.')
    ->setCode(function ($input, $output) {
        update_data_with_partitioned_dml(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Delete Data with Partitioned DML
$application->add((new Command('deleted-data-with-partitioned-dml'))
    ->setDefinition($inputDefinition)
    ->setDescription('Deletes sample data in the database by partition with a DML statement.')
    ->setCode(function ($input, $output) {
        delete_data_with_partitioned_dml(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Update data with Batch DML
$application->add((new Command('update-data-with-batch-dml'))
    ->setDefinition($inputDefinition)
    ->setDescription('Updates sample data in the given database using Batch DML.')
    ->setCode(function ($input, $output) {
        update_data_with_batch_dml(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Create table with supported datatypes
$application->add((new Command('create-table-with-datatypes'))
    ->setDefinition($inputDefinition)
    ->setDescription('Creates a table with supported datatypes.')
    ->setCode(function ($input, $output) {
        create_table_with_datatypes(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Insert data with supported datatypes
$application->add((new Command('insert-data-with-datatypes'))
    ->setDefinition($inputDefinition)
    ->setDescription('Inserts data with supported datatypes.')
    ->setCode(function ($input, $output) {
        insert_data_with_datatypes(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Query Data with Array Parameter
$application->add((new Command('query-data-with-array-parameter'))
    ->setDefinition($inputDefinition)
    ->setDescription('Queries sample data using SQL with an ARRAY parameter.')
    ->setCode(function ($input, $output) {
        query_data_with_array_parameter(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Query Data with Bool Parameter
$application->add((new Command('query-data-with-bool-parameter'))
    ->setDefinition($inputDefinition)
    ->setDescription('Queries sample data using SQL with a BOOL parameter.')
    ->setCode(function ($input, $output) {
        query_data_with_bool_parameter(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Query Data with Bytes Parameter
$application->add((new Command('query-data-with-bytes-parameter'))
    ->setDefinition($inputDefinition)
    ->setDescription('Queries sample data using SQL with a BYTES parameter.')
    ->setCode(function ($input, $output) {
        query_data_with_bytes_parameter(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Query Data with Date Parameter
$application->add((new Command('query-data-with-date-parameter'))
    ->setDefinition($inputDefinition)
    ->setDescription('Queries sample data using SQL with a DATE parameter.')
    ->setCode(function ($input, $output) {
        query_data_with_date_parameter(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Query Data with Float Parameter
$application->add((new Command('query-data-with-float-parameter'))
    ->setDefinition($inputDefinition)
    ->setDescription('Queries sample data using SQL with a FLOAT64 parameter.')
    ->setCode(function ($input, $output) {
        query_data_with_float_parameter(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Query Data with Int Parameter
$application->add((new Command('query-data-with-int-parameter'))
    ->setDefinition($inputDefinition)
    ->setDescription('Queries sample data using SQL with a INT64 parameter.')
    ->setCode(function ($input, $output) {
        query_data_with_int_parameter(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Query Data with String Parameter
$application->add((new Command('query-data-with-string-parameter'))
    ->setDefinition($inputDefinition)
    ->setDescription('Queries sample data using SQL with a STRING parameter.')
    ->setCode(function ($input, $output) {
        query_data_with_string_parameter(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Query Data with Timestamp Parameter
$application->add((new Command('query-data-with-timestamp-parameter'))
    ->setDefinition($inputDefinition)
    ->setDescription('Queries sample data using SQL with a TIMESTAMP parameter.')
    ->setCode(function ($input, $output) {
        query_data_with_timestamp_parameter(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Query Data with Query Options
$application->add((new Command('query-data-with-query-options'))
    ->setDefinition($inputDefinition)
    ->setDescription('Queries sample data using SQL with query options.')
    ->setCode(function ($input, $output) {
        query_data_with_query_options(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Create Client With Query Options
$application->add((new Command('create-client-with-query-options'))
    ->setDefinition($inputDefinition)
    ->setDescription('Create a client with query options.')
    ->setCode(function ($input, $output) {
        create_client_with_query_options(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

$application->add((new Command('create-backup'))
    ->setDefinition($idbInputDefinition)
    ->setDescription('Lists existing backups for instance.')
    ->setCode(function ($input, $output) {
        create_backup(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id'),
            $input->getArgument('backup_id')
        );
    })
);

$application->add((new Command('cancel-backup'))
    ->setDefinition($inputDefinition)
    ->setDescription('Cancels backup operation.')
    ->setCode(function ($input, $output) {
        cancel_backup_operation(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

$application->add((new Command('list-backups'))
    ->setDefinition($instanceInputDefinition)
    ->setDescription('Lists existing backups for instance.')
    ->setCode(function ($input, $output) {
        list_backups(
            $input->getArgument('instance_id')
        );
    })
);

$application->add((new Command('restore-backup'))
    ->setDefinition($idbInputDefinition)
    ->setDescription('Restore database from backup.')
    ->setCode(function ($input, $output) {
        restore_backup(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id'),
            $input->getArgument('backup_id')
        );
    })
);

$application->add((new Command('update-backup'))
    ->setDefinition($ibInputDefinition)
    ->setDescription('Update backup expire time.')
    ->setCode(function ($input, $output) {
        update_backup(
            $input->getArgument('instance_id'),
            $input->getArgument('backup_id')
        );
    })
);

$application->add((new Command('delete-backup'))
    ->setDefinition($ibInputDefinition)
    ->setDescription('Lists existing backups.')
    ->setCode(function ($input, $output) {
        delete_backup(
            $input->getArgument('instance_id'),
            $input->getArgument('backup_id')
        );
    })
);

$application->add((new Command('list-backup-operations'))
    ->setDefinition($inputDefinition)
    ->setDescription('Lists backup operations.')
    ->setCode(function ($input, $output) {
        list_backup_operations(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

$application->add((new Command('list-database-operations'))
    ->setDefinition($instanceInputDefinition)
    ->setDescription('Lists database operations.')
    ->setCode(function ($input, $output) {
        list_database_operations(
            $input->getArgument('instance_id')
        );
    })
);

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
