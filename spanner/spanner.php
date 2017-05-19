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

$application = new Application();

$inputDefinition = new InputDefinition([
    new InputArgument('instance_id', InputArgument::REQUIRED, 'The instance id'),
    new InputArgument('database_id', InputArgument::REQUIRED, 'The database id'),
]);

// Create Database command
$application->add((new Command('create-database'))
    ->setDefinition($inputDefinition)
    ->setDescription('Creates a database and tables for sample data.')
    ->setCode(function($input, $output) {
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
    ->setCode(function($input, $output) {
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
    ->setCode(function($input, $output) {
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
    ->setCode(function($input, $output) {
        read_data(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Query data with new column command
$application->add((new Command('query-data-with-new-column'))
    ->setDefinition($inputDefinition)
    ->setDescription('Queries sample data from the database using SQL.')
    ->setCode(function($input, $output) {
        query_data_with_new_column(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Create index command
$application->add((new Command('create-index'))
    ->setDefinition($inputDefinition)
    ->setDescription('Adds a simple index to the example database.')
    ->setCode(function($input, $output) {
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
    ->setCode(function($input, $output) {
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
    ->setCode(function($input, $output) {
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
    ->setCode(function($input, $output) {
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
    ->setCode(function($input, $output) {
        read_data_with_storing_index(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Add column command
$application->add((new Command('add-column'))
    ->setDefinition($inputDefinition)
    ->setDescription('Adds a new column to the Albums table in the example database.')
    ->setCode(function($input, $output) {
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
    ->setCode(function($input, $output) {
        update_data(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Read-write transaction command
$application->add((new Command('read-write-transaction'))
    ->setDefinition($inputDefinition)
    ->setDescription('Performs a read-write transaction to update two sample records in the database.')
    ->setCode(function($input, $output) {
        read_write_transaction(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

// Read-only transaction command
$application->add((new Command('read-only-transaction'))
    ->setDefinition($inputDefinition)
    ->setDescription('Reads data inside of a read-only transaction.')
    ->setCode(function($input, $output) {
        read_only_transaction(
            $input->getArgument('instance_id'),
            $input->getArgument('database_id')
        );
    })
);

$application->run();
