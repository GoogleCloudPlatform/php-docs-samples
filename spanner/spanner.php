<?php
/**
 * Copyright 2016 Google Inc.
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

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
