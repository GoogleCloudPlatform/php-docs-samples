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
use Symfony\Component\Console\Input\InputDefinition;
use Google\Cloud\Firestore\FirestoreClient;

# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

$application = new Application('Cloud Firestore');

$inputDefinition = new InputDefinition([
    new InputArgument('project', InputArgument::REQUIRED, 'Your Google Cloud Project ID'),
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
        initialize();
    })
);

// Initialize Project ID command
$application->add((new Command('initialize-project-id'))
    ->setDefinition($inputDefinition)
    ->setDescription('Initialize Cloud Firestore with given project ID.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command initializes Cloud Firestore using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        initialize_project_id($projectId);
    })
);

// Add Data command
$application->add((new Command('add-data'))
    ->setDefinition($inputDefinition)
    ->setDescription('Add data to a document.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command adds data to a document using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        add_data($projectId);
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
        $projectId = $input->getArgument('project');
        get_all($projectId);
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
        $projectId = $input->getArgument('project');
        set_document($projectId);
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
        $projectId = $input->getArgument('project');
        add_doc_data_types($projectId);
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
        $projectId = $input->getArgument('project');
        set_requires_id($projectId);
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
        $projectId = $input->getArgument('project');
        add_doc_data_with_auto_id($projectId);
    })
);

// Auto-Generate ID then Add Document Data command
$application->add((new Command('add-doc-data-after-auto-id'))
    ->setDefinition($inputDefinition)
    ->setDescription('Auto-generate an ID for a document, then add document data.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command auto-generates an ID for a document and then adds document data using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        add_doc_data_after_auto_id($projectId);
    })
);

// Query Create Examples command
$application->add((new Command('query-create-examples'))
    ->setDefinition($inputDefinition)
    ->setDescription('Create an example collection of documents.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command creates an example collection of documents using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        query_create_examples($projectId);
    })
);

// Create Query State command
$application->add((new Command('create-query-state'))
    ->setDefinition($inputDefinition)
    ->setDescription('Create a query that gets documents where state=CA.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command creates a query that gets documents where state=CA using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        create_query_state($projectId);
    })
);

// Create Query Capital command
$application->add((new Command('create-query-capital'))
    ->setDefinition($inputDefinition)
    ->setDescription('Create a query that gets documents where capital=True.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command creates a query that gets documents where capital=True using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        create_query_capital($projectId);
    })
);

// Simple Queries command
$application->add((new Command('simple-queries'))
    ->setDefinition($inputDefinition)
    ->setDescription('Create queries using single where clauses.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command creates queries using single where clauses using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        simple_queries($projectId);
    })
);

// Array Membership command
$application->add((new Command('array-membership'))
    ->setDefinition($inputDefinition)
    ->setDescription('Create queries using an array-contains where clause.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command creates queries using an array-contains where clause using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        array_membership($projectId);
    })
);

// Chained Query command
$application->add((new Command('chained-query'))
    ->setDefinition($inputDefinition)
    ->setDescription('Create a query with chained clauses.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command creates a query with chained clauses using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        chained_query($projectId);
    })
);

// Composite Index Chained Query command
$application->add((new Command('composite-index-chained-query'))
    ->setDefinition($inputDefinition)
    ->setDescription('Create a composite index chained query, which combines an equality operator with a range comparison.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command creates a composite index chained query using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        composite_index_chained_query($projectId);
    })
);

// Range Query command
$application->add((new Command('range-query'))
    ->setDefinition($inputDefinition)
    ->setDescription('Create a query with range clauses.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command creates a query with range clauses using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        range_query($projectId);
    })
);

// Invalid Range Query command
$application->add((new Command('invalid-range-query'))
    ->setDefinition($inputDefinition)
    ->setDescription('An example of an invalid range query.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command creates an example of an invalid range query using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        invalid_range_query($projectId);
    })
);

// Delete Document command
$application->add((new Command('delete-document'))
    ->setDefinition($inputDefinition)
    ->setDescription('Delete a document.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command deletes a document using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        delete_doc($projectId);
    })
);

// Delete Field command
$application->add((new Command('delete-field'))
    ->setDefinition($inputDefinition)
    ->setDescription('Delete a field from a document.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command deletes a field from a document using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        delete_field($projectId);
    })
);

// Delete Collection command
$application->add((new Command('delete-collection'))
    ->setDefinition($inputDefinition)
    ->setDescription('Delete a collection.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command deletes a collection using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        $db = new FirestoreClient([
            'projectId' => $projectId,
        ]);
        $cityCollection = $db->collection('cities');
        delete_collection($cityCollection, 2);
    })
);

// Retrieve Create Examples command
$application->add((new Command('retrieve-create-examples'))
    ->setDefinition($inputDefinition)
    ->setDescription('Create an example collection of documents.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command creates an example collection of documents using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        retrieve_create_examples($projectId);
    })
);

// Get Document command
$application->add((new Command('get-document'))
    ->setDefinition($inputDefinition)
    ->setDescription('Get a document.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command gets a document using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        get_document($projectId);
    })
);

// Get Multiple Documents command
$application->add((new Command('get-multiple-docs'))
    ->setDefinition($inputDefinition)
    ->setDescription('Get multiple documents from a collection.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command gets a multiple documents from a collection using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        get_multiple_docs($projectId);
    })
);

// Get All Documents command
$application->add((new Command('get-all-docs'))
    ->setDefinition($inputDefinition)
    ->setDescription('Get all documents in a collection.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command gets all documents in a collection using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        get_all_docs($projectId);
    })
);

// Add Subcollection command
$application->add((new Command('add-subcollection'))
    ->setDefinition($inputDefinition)
    ->setDescription('Add a subcollection by creating a new document.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command adds a subcollection by creating a new document using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        $db = new FirestoreClient([
            'projectId' => $projectId,
        ]);
        $cityRef = $db->collection('cities')->document('SF');
        $subcollectionRef = $cityRef->collection('neighborhoods');
        $data = [
            'name' => 'Marina',
        ];
        $subcollectionRef->document('Marina')->set($data);
    })
);

// List Subcollections command
$application->add((new Command('list-subcollections'))
    ->setDefinition($inputDefinition)
    ->setDescription('List subcollections of a document.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command lists subcollections of a document using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        list_subcollections($projectId);
    })
);

// Order By Name Limit Query command
$application->add((new Command('order-by-name-limit-query'))
    ->setDefinition($inputDefinition)
    ->setDescription('Create an order by name with limit query.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command creates an order by name with limit query using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        order_by_name_limit_query($projectId);
    })
);

// Order By Name Descending Limit Query command
$application->add((new Command('order-by-name-desc-limit-query'))
    ->setDefinition($inputDefinition)
    ->setDescription('Create an order by name descending with limit query.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command creates an order by name descending with limit query using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        order_by_name_desc_limit_query($projectId);
    })
);

// Order By State and Population Query command
$application->add((new Command('order-by-state-and-population-query'))
    ->setDefinition($inputDefinition)
    ->setDescription('Create an order by state and descending population query.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command creates an order by state and descending population query using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        order_by_state_and_population_query($projectId);
    })
);

// Where Order By Limit Query command
$application->add((new Command('where-order-by-limit-query'))
    ->setDefinition($inputDefinition)
    ->setDescription('Combine where with order by and limit in a query.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command combines where with order by and limit in a query using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        where_order_by_limit_query($projectId);
    })
);

// Range Order By Query command
$application->add((new Command('range-order-by-query'))
    ->setDefinition($inputDefinition)
    ->setDescription('Create a range with order by query.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command creates a range with order by query using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        range_order_by_query($projectId);
    })
);

// Invalid Range Order By Query command
$application->add((new Command('invalid-range-order-by-query'))
    ->setDefinition($inputDefinition)
    ->setDescription('An invalid range with order by query.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command creates an invalid range with order by query using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        invalid_range_order_by_query($projectId);
    })
);

// Document Reference command
$application->add((new Command('document-ref'))
    ->setDefinition($inputDefinition)
    ->setDescription('Get a document reference.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command gets a document reference using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        document_ref($projectId);
    })
);

// Collection Reference command
$application->add((new Command('collection-ref'))
    ->setDefinition($inputDefinition)
    ->setDescription('Get a collection reference.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command gets a collection reference using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        collection_ref($projectId);
    })
);

// Document Path Reference command
$application->add((new Command('document-path-ref'))
    ->setDefinition($inputDefinition)
    ->setDescription('Get a document path reference.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command gets a document path reference using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        document_path_ref($projectId);
    })
);

// Subcollection Reference command
$application->add((new Command('subcollection-ref'))
    ->setDefinition($inputDefinition)
    ->setDescription('Get a reference to a subcollection document.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command gets a reference to a subcollection document using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        subcollection_ref($projectId);
    })
);

// Update Document command
$application->add((new Command('update-doc'))
    ->setDefinition($inputDefinition)
    ->setDescription('Update a document.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command updates a document using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        update_doc($projectId);
    })
);

// Update Document Array command
$application->add((new Command('update-doc-array'))
    ->setDefinition($inputDefinition)
    ->setDescription('Update a document array field.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command updates a document array field using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        update_doc_array($projectId);
    })
);

// Update Document Increment command
$application->add((new Command('update-doc-increment'))
    ->setDefinition($inputDefinition)
    ->setDescription('Update a document number field using Increment.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command updates a document number field using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        update_doc_increment($projectId);
    })
);

// Set Document Merge command
$application->add((new Command('set-document-merge'))
    ->setDefinition($inputDefinition)
    ->setDescription('Set document data by merging it into the existing document.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command sets document data by merging it into the existing document using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        set_document_merge($projectId);
    })
);

// Update Nested Fields command
$application->add((new Command('update-nested-fields'))
    ->setDefinition($inputDefinition)
    ->setDescription('Update fields in nested data.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command updates fields in nested data using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        update_nested_fields($projectId);
    })
);

// Update Field With Server Timestamp command
$application->add((new Command('update-server-timestamp'))
    ->setDefinition($inputDefinition)
    ->setDescription('Update field with server timestamp.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command updates a field with the server timestamp using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        update_server_timestamp($projectId);
    })
);

// Run Simple Transaction command
$application->add((new Command('run-simple-transaction'))
    ->setDefinition($inputDefinition)
    ->setDescription('Run a simple transaction.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command runs a simple transaction using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        run_simple_transaction($projectId);
    })
);

// Return Info Transaction command
$application->add((new Command('return-info-transaction'))
    ->setDefinition($inputDefinition)
    ->setDescription('Return information from your transaction.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command returns information from your transaction using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        return_info_transaction($projectId);
    })
);

// Batch Write command
$application->add((new Command('batch-write'))
    ->setDefinition($inputDefinition)
    ->setDescription('Batch write.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command batch writes using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        batch_write($projectId);
    })
);

// Start At Field Query Cursor command
$application->add((new Command('start-at-field-query-cursor'))
    ->setDefinition($inputDefinition)
    ->setDescription('Define field start point for a query.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command defines a field start point for a query using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        start_at_field_query_cursor($projectId);
    })
);

// End At Field Query Cursor command
$application->add((new Command('end-at-field-query-cursor'))
    ->setDefinition($inputDefinition)
    ->setDescription('Define field end point for a query.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command defines a field end point for a query using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        end_at_field_query_cursor($projectId);
    })
);

// Start At Snapshot Query Cursor command
$application->add((new Command('start-at-snapshot-query-cursor'))
    ->setDefinition($inputDefinition)
    ->setDescription('Define snapshot start point for a query.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command defines a snapshot start point for a query using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        start_at_snapshot_query_cursor($projectId);
    })
);

// Paginated Query Cursor command
$application->add((new Command('paginated-query-cursor'))
    ->setDefinition($inputDefinition)
    ->setDescription('Paginate using cursor queries.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command paginates using query cursors using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        paginated_query_cursor($projectId);
    })
);

// Multple Cursor Conditions command
$application->add((new Command('multiple-cursor-conditions'))
    ->setDefinition($inputDefinition)
    ->setDescription('Set multiple cursor conditions.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command sets multiple cursor conditions using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        multiple_cursor_conditions($projectId);
    })
);

// Delete Test Collections command
$application->add((new Command('delete-test-collections'))
    ->setDefinition($inputDefinition)
    ->setDescription('Delete test collections used in these code samples.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command deletes test collections used in these code samples using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        $db = new FirestoreClient([
            'projectId' => $projectId,
        ]);
        $subcollection = $db->collection('cities/SF/neighborhoods');
        delete_collection($subcollection, 2);
        $cityCollection = $db->collection('cities');
        delete_collection($cityCollection, 2);
        $dataCollection = $db->collection('data');
        delete_collection($dataCollection, 2);
        $usersCollection = $db->collection('users');
        delete_collection($usersCollection, 2);
        $objectsCollection = $db->collection('objects');
        delete_collection($objectsCollection, 2);
    })
);

//Create distributed counter
$application->add((new Command('initialize-distributed-counter'))
    ->setDefinition($inputDefinition)
    ->setDescription('Creates a subcollection from the specified multiple shards.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command creates a distributed counter as a sub collection in Google Cloud Firestore, consisting of several empty shards.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        initialize_distributed_counter($projectId);
    })
);

//Increment (distributed counter)
$application->add((new Command('update-distributed-counter'))
    ->setDefinition($inputDefinition)
    ->setDescription('Increments a randomly picked shard of a distributed counter.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command increments the randomly selected shard of a distributed counter using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        update_distributed_counter($projectId);
    })
);

//get value (distributed counter)
$application->add((new Command('get-distributed-counter-value'))
    ->setDefinition($inputDefinition)
    ->setDescription('Returns the total count across all the shards of a distributed counter.')
    ->setHelp(<<<EOF
The <info>%command.name%</info> command returns the total count across all the shards of a distributed counter, using the Google Cloud Firestore API.

    <info>php %command.full_name%</info>

EOF
    )
    ->setCode(function ($input, $output) {
        $projectId = $input->getArgument('project');
        get_distributed_counter_value($projectId);
    })
);

// for testing
if (getenv('PHPUNIT_TESTS') === '1') {
    return $application;
}

$application->run();
