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

namespace Google\Cloud\Samples\Firestore\Tests;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;


/**
 * Unit Tests for Firestore commands.
 */
class firestoreTest extends \PHPUnit_Framework_TestCase
{
    protected static $hasCredentials;

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
    }

    public function setUp()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
    }

    public function testInitialize()
    {
        $output = $this->runCommand('initialize');
        $this->assertContains('Created Cloud Firestore client with default project ID.', $output);
    }

    public function testInitializeProjectId()
    {
        $output = $this->runCommand('initialize-project-id');
        $this->assertContains('Created Cloud Firestore client with project ID:', $output);
    }

    public function testAddData()
    {
        $output = $this->runCommand('add-data');
        $this->assertContains('Added data to the lovelace document in the users collection.', $output);
        $this->assertContains('Added data to the aturing document in the users collection.', $output);
    }

    public function testRetrieveAllDocuments()
    {
        $output = $this->runCommand('retrieve-all-documents');
        $this->assertContains('User:', $output);
        $this->assertContains('First: Ada', $output);
        $this->assertContains('Last: Lovelace', $output);
        $this->assertContains('Born: 1815', $output);
        $this->assertContains('First: Alan', $output);
        $this->assertContains('Middle: Mathison', $output);
        $this->assertContains('Last: Turing', $output);
        $this->assertContains('Born: 1912', $output);
        $this->assertContains('Retrieved and printed out all documents from the users collection.', $output);
    }

    public function testSetDocument()
    {
        $output = $this->runCommand('set-document');
        $this->assertContains('Set data for the LA document in the cities collection.', $output);
    }

    public function testAddDocDataTypes()
    {
        $output = $this->runCommand('add-doc-data-types');
        $this->assertContains('Set multiple data-type data for the one document in the data collection.', $output);
    }

    public function testSetRequiresId()
    {
        $output = $this->runCommand('set-requires-id');
        $this->assertContains('Added document with ID: new-city-id', $output);
    }

    public function testAddDocDataWithAutoId()
    {
        $output = $this->runCommand('add-doc-data-with-auto-id');
        $this->assertContains('Added document with ID:', $output);
    }

    public function testAddDocDataAfterAutoId()
    {
        $output = $this->runCommand('add-doc-data-after-auto-id');
        $this->assertContains('Added document with ID:', $output);
    }

    public function testQueryCreateExamples()
    {
        $output = $this->runCommand('query-create-examples');
        $this->assertContains('Added example cities data to the cities collection.', $output);
    }

    public function testCreateQueryState()
    {
        $output = $this->runCommand('create-query-state');
        $this->assertContains('Document SF returned by query state=CA', $output);
        $this->assertContains('Document LA returned by query state=CA', $output);
    }

    public function testCreateQueryCapital()
    {
        $output = $this->runCommand('create-query-capital');
        $this->assertContains('Document BJ returned by query capital=true', $output);
        $this->assertContains('Document DC returned by query capital=true', $output);
        $this->assertContains('Document TOK returned by query capital=true', $output);
    }

    public function testSimpleQueries()
    {
        $output = $this->runCommand('simple-queries');
        $this->assertContains('Document LA returned by query state=CA', $output);
        $this->assertContains('Document SF returned by query state=CA', $output);
        $this->assertContains('Document BJ returned by query population>1000000', $output);
        $this->assertContains('Document LA returned by query population>1000000', $output);
        $this->assertContains('Document TOK returned by query population>1000000', $output);
        $this->assertContains('Document SF returned by query name>=San Francisco', $output);
        $this->assertContains('Document TOK returned by query name>=San Francisco', $output);
    }

    public function testChainedQuery()
    {
        $output = $this->runCommand('chained-query');
        $this->assertContains('Document SF returned by query state=CA and name=San Francisco', $output);
    }

    public function testCompositeIndexChainedQuery()
    {
        $output = $this->runCommand('composite-index-chained-query');
        $this->assertContains('Document SF returned by query state=CA and population<1000000', $output);
    }

    public function testRangeQuery()
    {
        $output = $this->runCommand('range-query');
        $this->assertContains('Document LA returned by query CA<=state<=IN', $output);
        $this->assertContains('Document SF returned by query CA<=state<=IN', $output);
    }

    public function testInvalidRangeQuery()
    {
        $output = $this->runCommand('invalid-range-query');
    }

    public function testDeleteDocument()
    {
        $output = $this->runCommand('delete-document');
        $this->assertContains('Deleted the DC document in the cities collection.', $output);
    }

    public function testDeleteField()
    {
        $output = $this->runCommand('delete-field');
        $this->assertContains('Deleted the capital field from the BJ document in the cities collection.', $output);
    }

    public function testDeleteCollection()
    {
        $output = $this->runCommand('delete-collection');
        $this->assertContains('Deleting document BJ', $output);
        $this->assertContains('Deleting document LA', $output);
        $this->assertContains('Deleting document TOK', $output);
        $this->assertContains('Deleting document SF', $output);
    }

    public function testRetrieveCreateExamples()
    {
        $output = $this->runCommand('retrieve-create-examples');
        $this->assertContains('Added example cities data to the cities collection.', $output);
    }

    public function testGetDocument()
    {
        $output = $this->runCommand('get-document');
        $this->assertContains('Document data:', $output);
        $this->assertContains('[population] => 860000', $output);
        $this->assertContains('[state] => CA', $output);
        $this->assertContains('[capital] =>', $output);
        $this->assertContains('[name] => San Francisco', $output);
        $this->assertContains('[country] => USA', $output);
    }

    public function testGetMultipleDocs()
    {
        $output = $this->runCommand('get-multiple-docs');
        $this->assertContains('Document data for document DC:', $output);
        $this->assertContains('Document data for document TOK:', $output);
        $this->assertContains('[name] => Washington D.C.', $output);
        $this->assertContains('[name] => Tokyo', $output);
    }

    public function testGetAllDocs()
    {
        $output = $this->runCommand('get-all-docs');
        $this->assertContains('Document data for document LA:', $output);
        $this->assertContains('[name] => Los Angeles', $output);
    }

    public function testOrderByNameLimitQuery()
    {
        $output = $this->runCommand('order-by-name-limit-query');
        $this->assertContains('Document BJ returned by order by name with limit query', $output);
        $this->assertContains('Document LA returned by order by name with limit query', $output);
        $this->assertContains('Document SF returned by order by name with limit query', $output);
    }

    public function testOrderByNameDescLimitQuery()
    {
        $output = $this->runCommand('order-by-name-desc-limit-query');
        $this->assertContains('Document DC returned by order by name descending with limit query', $output);
        $this->assertContains('Document TOK returned by order by name descending with limit query', $output);
        $this->assertContains('Document SF returned by order by name descending with limit query', $output);
    }

    public function testOrderByStateAndPopulationQuery()
    {
        $output = $this->runCommand('order-by-state-and-population-query');
        $this->assertContains('Document LA returned by order by state and descending population query', $output);
        $this->assertContains('Document SF returned by order by state and descending population query', $output);
        $this->assertContains('Document BJ returned by order by state and descending population query', $output);
        $this->assertContains('Document DC returned by order by state and descending population query', $output);
        $this->assertContains('Document TOK returned by order by state and descending population query', $output);
    }

    public function testWhereOrderByLimitQuery()
    {
        $output = $this->runCommand('where-order-by-limit-query');
        $this->assertContains('Document LA returned by where order by limit query', $output);
        $this->assertContains('Document TOK returned by where order by limit query', $output);
    }

    public function testRangeOrderByQuery()
    {
        $output = $this->runCommand('range-order-by-query');
        $this->assertContains('Document LA returned by range with order by query', $output);
        $this->assertContains('Document TOK returned by range with order by query', $output);
        $this->assertContains('Document BJ returned by range with order by query', $output);
    }

    public function testInvalidRangeOrderByQuery()
    {
        $output = $this->runCommand('invalid-range-order-by-query');
    }

    public function testDocumentRef()
    {
        $output = $this->runCommand('document-ref');
    }

    public function testCollectionRef()
    {
        $output = $this->runCommand('collection-ref');
    }

    public function testDocumentPathRef()
    {
        $output = $this->runCommand('document-path-ref');
    }

    public function testSubcollectionRef()
    {
        $output = $this->runCommand('subcollection-ref');
    }

    public function testUpdateDoc()
    {
        $output = $this->runCommand('update-doc');
        $this->assertContains('Updated the capital field of the DC document in the cities collection.', $output);
    }

    public function testSetDocumentMerge()
    {
        $output = $this->runCommand('set-document-merge');
        $this->assertContains('Set document data by merging it into the existing BJ document in the cities collection.', $output);
    }

    public function testUpdateNestedFields()
    {
        $output = $this->runCommand('update-nested-fields');
        $this->assertContains('Updated the age and favorite color fields of the frank document in the users collection.', $output);
    }

    public function testUpdateServerTimestamp()
    {
        $output = $this->runCommand('update-server-timestamp');
        $this->assertContains('Updated the timestamp field of the some-id document in the objects collection.', $output);
    }

    public function testRunSimpleTransaction()
    {
        $output = $this->runCommand('run-simple-transaction');
        $this->assertContains('Ran a simple transaction to update the population field in the SF document in the cities collection.', $output);
    }

    public function testReturnInfoTransaction()
    {
        $output = $this->runCommand('return-info-transaction');
        $this->assertContains('Population updated successfully.', $output);
    }

    public function testBatchWrite()
    {
        $output = $this->runCommand('batch-write');
        $this->assertContains('Batch write successfully completed.', $output);
    }

    public function testStartAtFieldQueryCursor()
    {
        $output = $this->runCommand('start-at-field-query-cursor');
        $this->assertContains('Document SF returned by start at population 1000000 field query cursor.', $output);
        $this->assertContains('Document TOK returned by start at population 1000000 field query cursor.', $output);
        $this->assertContains('Document BJ returned by start at population 1000000 field query cursor.', $output);
    }

    public function testEndAtFieldQueryCursor()
    {
        $output = $this->runCommand('end-at-field-query-cursor');
        $this->assertContains('Document DC returned by end at population 1000000 field query cursor.', $output);
        $this->assertContains('Document SF returned by end at population 1000000 field query cursor.', $output);
    }

    public function testPaginatedQueryCursor()
    {
        $output = $this->runCommand('paginated-query-cursor');
        $this->assertContains('Document BJ returned by paginated query cursor.', $output);
    }

    public function testMultipleCursorConditions()
    {
        $output = $this->runCommand('multiple-cursor-conditions');
    }

    public function testDeleteTestCollections()
    {
        $output = $this->runCommand('delete-test-collections');
    }


    private function runCommand($commandName)
    {
        $application = require __DIR__ . '/../firestore.php';
        $command = $application->get($commandName);
        $commandTester = new CommandTester($command);

        ob_start();
        $commandTester->execute([
            'project' => getenv('FIRESTORE_PROJECT_ID'),
        ], [
            'interactive' => false
        ]);
        return ob_get_clean();
    }
}
