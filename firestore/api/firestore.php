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

namespace Google\Cloud\Samples\Firestore;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputDefinition;

# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

$application = new Application('Cloud Firestore');

$inputDefinition = new InputDefinition([
    new InputOption('project', 'p', InputOption::VALUE_OPTIONAL, 'Your Google Cloud Project ID'),
]);

// Initialize command
$application->add((new Command('initialize'))
	->setDefinition($inputDefinition)
    ->setDescription('Initialize Cloud Firestore with default project ID.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command initializes Cloud Firestore using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
    	$projectId = $input->getOption('project');
    	if ($projectId) {
            fs_initialize_project_id($projectId);
    	} else {
    		fs_initialize();
    	}
    })
);

// Add Data #1 command
$application->add((new Command('add-data-1'))
	->setDefinition($inputDefinition)
    ->setDescription('Add data to a document.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command adds data to a document using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
    	fs_add_data_1();
    })
);

// Add Data #2 command
$application->add((new Command('add-data-2'))
	->setDefinition($inputDefinition)
    ->setDescription('Add data to a document.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command adds data to a document using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
    	fs_add_data_2();
    })
);

// Retrieve All Documents command
$application->add((new Command('retrieve-all-documents'))
	->setDefinition($inputDefinition)
    ->setDescription('Retrieve all documents from a collection.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command retrieves all documents from a collection using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
    	fs_get_all();
    })
);

// Set Document command
$application->add((new Command('set-document'))
	->setDefinition($inputDefinition)
    ->setDescription('Set document data.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command sets document data using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
    	fs_set_document();
    })
);

// Data Types command
$application->add((new Command('add-doc-data-types'))
	->setDefinition($inputDefinition)
    ->setDescription('Set document data with different data types.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command sets document data with different data types using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
    	fs_add_doc_data_types();
    })
);

// Set Document Requires ID command
$application->add((new Command('set-requires-id'))
	->setDefinition($inputDefinition)
    ->setDescription('Set document data with a given document ID.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command sets document data with a given document ID using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
    	fs_set_requires_id();
    })
);

// Add Document Auto-Generated ID command
$application->add((new Command('add-doc-data-with-auto-id'))
	->setDefinition($inputDefinition)
    ->setDescription('Add document data with an auto-generated ID.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command adds document data with an auto-generated ID using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
    	fs_add_doc_data_with_auto_id();
    })
);

// Auto-Generate ID then Add Document Data command
$application->add((new Command('add-doc-data-after-auto-id'))
	->setDefinition($inputDefinition)
    ->setDescription('Auto-generate an ID for a document, then add document data.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> auto-generates an ID for a document and then adds document data using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
    	fs_add_doc_data_after_auto_id();
    })
);

// Query Create Examples command
$application->add((new Command('query-create-examples'))
	->setDefinition($inputDefinition)
    ->setDescription('Create an example collection of documents.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> creates an example collection of documents using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
    	fs_query_create_examples();
    })
);

// Create Query State command
$application->add((new Command('create-query-state'))
	->setDefinition($inputDefinition)
    ->setDescription('Create a query that gets documents where state=CA.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> creates a query that gets documents where state=CA using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
    	fs_create_query_state();
    })
);

// Create Query Capital command
$application->add((new Command('create-query-capital'))
	->setDefinition($inputDefinition)
    ->setDescription('Create a query that gets documents where capital=True.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> creates a query that gets documents where capital=True using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
    	fs_create_query_capital();
    })
);

// Simple Queries command
$application->add((new Command('simple-queries'))
	->setDefinition($inputDefinition)
    ->setDescription('Create queries using single where clauses.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> creates queries using single where clauses using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
    	fs_simple_queries();
    })
);

// Chained Query command
$application->add((new Command('chained-query'))
	->setDefinition($inputDefinition)
    ->setDescription('Create a query with chained clauses.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> creates a query with chained clauses using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
    	fs_chained_query();
    })
);

// Composite Index Chained Query command
$application->add((new Command('composite-index-chained-query'))
	->setDefinition($inputDefinition)
    ->setDescription('Create a composite index chained query, which combines an equality operator with a range comparison.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> creates a composite index chained query using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
    	fs_composite_index_chained_query();
    })
);

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

// Range Query command
$application->add((new Command('range-query'))
	->setDefinition($inputDefinition)
    ->setDescription('Create a query with range clauses.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> creates a a query with range clauses using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
    	fs_range_query();
    })
);

// Invalid Range Query command
$application->add((new Command('invalid-range-query'))
	->setDefinition($inputDefinition)
    ->setDescription('An example of an invalid range query.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> is an example of an invalid range query using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
    	fs_invalid_range_query();
    })
);


// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
