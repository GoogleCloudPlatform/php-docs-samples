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

use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for Firestore commands.
 */
class firestoreTest extends TestCase
{
    use TestTrait;
    use ExecuteCommandTrait;

    private static $commandFile = __DIR__ . '/../firestore.php';
    private static $firestoreProjectId;

    public static function setUpBeforeClass()
    {
        if (!extension_loaded('grpc')) {
            self::markTestSkipped('Must enable grpc extension.');
        }
        self::$firestoreProjectId = self::requireEnv('FIRESTORE_PROJECT_ID');
    }

    public function testInitialize()
    {
        $output = $this->runFirestoreCommand('initialize');
        $this->assertContains('Created Cloud Firestore client with default project ID.', $output);
    }

    public function testInitializeProjectId()
    {
        $output = $this->runFirestoreCommand('initialize-project-id');
        $this->assertContains('Created Cloud Firestore client with project ID:', $output);
    }

    public function testAddData()
    {
        $output = $this->runFirestoreCommand('add-data');
        $this->assertContains('Added data to the lovelace document in the users collection.', $output);
        $this->assertContains('Added data to the aturing document in the users collection.', $output);
    }

    /**
     * @depends testAddData
     */
    public function testRetrieveAllDocuments()
    {
        $output = $this->runFirestoreCommand('retrieve-all-documents');
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

    /**
     * @depends testAddData
     */
    public function testSetDocument()
    {
        $output = $this->runFirestoreCommand('set-document');
        $this->assertContains('Set data for the LA document in the cities collection.', $output);
    }

    /**
     * @depends testAddData
     */
    public function testAddDocDataTypes()
    {
        $output = $this->runFirestoreCommand('add-doc-data-types');
        $this->assertContains('Set multiple data-type data for the one document in the data collection.', $output);
    }

    /**
     * @depends testAddData
     */
    public function testSetRequiresId()
    {
        $output = $this->runFirestoreCommand('set-requires-id');
        $this->assertContains('Added document with ID: new-city-id', $output);
    }

    /**
     * @depends testAddData
     */
    public function testAddDocDataWithAutoId()
    {
        $output = $this->runFirestoreCommand('add-doc-data-with-auto-id');
        $this->assertContains('Added document with ID:', $output);
    }

    /**
     * @depends testAddData
     */
    public function testAddDocDataAfterAutoId()
    {
        $output = $this->runFirestoreCommand('add-doc-data-after-auto-id');
        $this->assertContains('Added document with ID:', $output);
    }

    public function testQueryCreateExamples()
    {
        $output = $this->runFirestoreCommand('query-create-examples');
        $this->assertContains('Added example cities data to the cities collection.', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testCreateQueryState()
    {
        $output = $this->runFirestoreCommand('create-query-state');
        $this->assertContains('Document SF returned by query state=CA', $output);
        $this->assertContains('Document LA returned by query state=CA', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testCreateQueryCapital()
    {
        $output = $this->runFirestoreCommand('create-query-capital');
        $this->assertContains('Document BJ returned by query capital=true', $output);
        $this->assertContains('Document DC returned by query capital=true', $output);
        $this->assertContains('Document TOK returned by query capital=true', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testSimpleQueries()
    {
        $output = $this->runFirestoreCommand('simple-queries');
        $this->assertContains('Document LA returned by query state=CA', $output);
        $this->assertContains('Document SF returned by query state=CA', $output);
        $this->assertContains('Document BJ returned by query population>1000000', $output);
        $this->assertContains('Document LA returned by query population>1000000', $output);
        $this->assertContains('Document TOK returned by query population>1000000', $output);
        $this->assertContains('Document SF returned by query name>=San Francisco', $output);
        $this->assertContains('Document TOK returned by query name>=San Francisco', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testArrayMembership()
    {
        $output = $this->runFirestoreCommand('array-membership');
        $this->assertContains('Document LA returned by query regions array-contains west_coast', $output);
        $this->assertContains('Document SF returned by query regions array-contains west_coast', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testChainedQuery()
    {
        $output = $this->runFirestoreCommand('chained-query');
        $this->assertContains('Document SF returned by query state=CA and name=San Francisco', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testCompositeIndexChainedQuery()
    {
        $output = $this->runFirestoreCommand('composite-index-chained-query');
        $this->assertContains('Document SF returned by query state=CA and population<1000000', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testRangeQuery()
    {
        $output = $this->runFirestoreCommand('range-query');
        $this->assertContains('Document LA returned by query CA<=state<=IN', $output);
        $this->assertContains('Document SF returned by query CA<=state<=IN', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testInvalidRangeQuery()
    {
        $output = $this->runFirestoreCommand('invalid-range-query');
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testDeleteDocument()
    {
        $output = $this->runFirestoreCommand('delete-document');
        $this->assertContains('Deleted the DC document in the cities collection.', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testDeleteField()
    {
        $output = $this->runFirestoreCommand('delete-field');
        $this->assertContains('Deleted the capital field from the BJ document in the cities collection.', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testDeleteCollection()
    {
        $output = $this->runFirestoreCommand('delete-collection');
        $this->assertContains('Deleting document BJ', $output);
        $this->assertContains('Deleting document LA', $output);
        $this->assertContains('Deleting document TOK', $output);
        $this->assertContains('Deleting document SF', $output);
    }

    public function testRetrieveCreateExamples()
    {
        $output = $this->runFirestoreCommand('retrieve-create-examples');
        $this->assertContains('Added example cities data to the cities collection.', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testGetDocument()
    {
        $output = $this->runFirestoreCommand('get-document');
        $this->assertContains('Document data:', $output);
        $this->assertContains('[population] => 860000', $output);
        $this->assertContains('[state] => CA', $output);
        $this->assertContains('[capital] =>', $output);
        $this->assertContains('[name] => San Francisco', $output);
        $this->assertContains('[country] => USA', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testGetMultipleDocs()
    {
        $output = $this->runFirestoreCommand('get-multiple-docs');
        $this->assertContains('Document data for document DC:', $output);
        $this->assertContains('Document data for document TOK:', $output);
        $this->assertContains('[name] => Washington D.C.', $output);
        $this->assertContains('[name] => Tokyo', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testGetAllDocs()
    {
        $output = $this->runFirestoreCommand('get-all-docs');
        $this->assertContains('Document data for document LA:', $output);
        $this->assertContains('[name] => Los Angeles', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testListSubcollections()
    {
        $this->runFirestoreCommand('add-subcollection');
        $output = $this->runFirestoreCommand('list-subcollections');
        $this->assertContains('Found subcollection with id: neighborhoods', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testOrderByNameLimitQuery()
    {
        $output = $this->runFirestoreCommand('order-by-name-limit-query');
        $this->assertContains('Document BJ returned by order by name with limit query', $output);
        $this->assertContains('Document LA returned by order by name with limit query', $output);
        $this->assertContains('Document SF returned by order by name with limit query', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testOrderByNameDescLimitQuery()
    {
        $output = $this->runFirestoreCommand('order-by-name-desc-limit-query');
        $this->assertContains('Document DC returned by order by name descending with limit query', $output);
        $this->assertContains('Document TOK returned by order by name descending with limit query', $output);
        $this->assertContains('Document SF returned by order by name descending with limit query', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testOrderByStateAndPopulationQuery()
    {
        $output = $this->runFirestoreCommand('order-by-state-and-population-query');
        $this->assertContains('Document LA returned by order by state and descending population query', $output);
        $this->assertContains('Document SF returned by order by state and descending population query', $output);
        $this->assertContains('Document BJ returned by order by state and descending population query', $output);
        $this->assertContains('Document DC returned by order by state and descending population query', $output);
        $this->assertContains('Document TOK returned by order by state and descending population query', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testWhereOrderByLimitQuery()
    {
        $output = $this->runFirestoreCommand('where-order-by-limit-query');
        $this->assertContains('Document LA returned by where order by limit query', $output);
        $this->assertContains('Document TOK returned by where order by limit query', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testRangeOrderByQuery()
    {
        $output = $this->runFirestoreCommand('range-order-by-query');
        $this->assertContains('Document LA returned by range with order by query', $output);
        $this->assertContains('Document TOK returned by range with order by query', $output);
        $this->assertContains('Document BJ returned by range with order by query', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testInvalidRangeOrderByQuery()
    {
        $output = $this->runFirestoreCommand('invalid-range-order-by-query');
    }

    public function testDocumentRef()
    {
        $output = $this->runFirestoreCommand('document-ref');
    }

    public function testCollectionRef()
    {
        $output = $this->runFirestoreCommand('collection-ref');
    }

    public function testDocumentPathRef()
    {
        $output = $this->runFirestoreCommand('document-path-ref');
    }

    public function testSubcollectionRef()
    {
        $output = $this->runFirestoreCommand('subcollection-ref');
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testUpdateDoc()
    {
        $output = $this->runFirestoreCommand('update-doc');
        $this->assertContains('Updated the capital field of the DC document in the cities collection.', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testUpdateDocArray()
    {
        $output = $this->runFirestoreCommand('update-doc-array');
        $this->assertContains('Updated the regions field of the DC document in the cities collection.', $output);
    }

    /**
     * @depends testUpdateDoc
     */
    public function testSetDocumentMerge()
    {
        $output = $this->runFirestoreCommand('set-document-merge');
        $this->assertContains('Set document data by merging it into the existing BJ document in the cities collection.', $output);
    }

    /**
     * @depends testSetDocumentMerge
     */
    public function testUpdateNestedFields()
    {
        $output = $this->runFirestoreCommand('update-nested-fields');
        $this->assertContains('Updated the age and favorite color fields of the frank document in the users collection.', $output);
    }

    /**
     * @depends testUpdateNestedFields
     */
    public function testUpdateServerTimestamp()
    {
        $output = $this->runFirestoreCommand('update-server-timestamp');
        $this->assertContains('Updated the timestamp field of the some-id document in the objects collection.', $output);
    }

    /**
     * @depends testUpdateServerTimestamp
     */
    public function testRunSimpleTransaction()
    {
        $output = $this->runFirestoreCommand('run-simple-transaction');
        $this->assertContains('Ran a simple transaction to update the population field in the SF document in the cities collection.', $output);
    }

    /**
     * @depends testRunSimpleTransaction
     */
    public function testReturnInfoTransaction()
    {
        $output = $this->runFirestoreCommand('return-info-transaction');
        $this->assertContains('Population updated successfully.', $output);
    }

    /**
     * @depends testReturnInfoTransaction
     */
    public function testBatchWrite()
    {
        $output = $this->runFirestoreCommand('batch-write');
        $this->assertContains('Batch write successfully completed.', $output);
    }

    /**
     * @depends testBatchWrite
     */
    public function testStartAtFieldQueryCursor()
    {
        $output = $this->runFirestoreCommand('start-at-field-query-cursor');
        $this->assertContains('Document SF returned by start at population 1000000 field query cursor.', $output);
        $this->assertContains('Document TOK returned by start at population 1000000 field query cursor.', $output);
        $this->assertContains('Document BJ returned by start at population 1000000 field query cursor.', $output);
    }

    /**
     * @depends testStartAtFieldQueryCursor
     */
    public function testEndAtFieldQueryCursor()
    {
        $output = $this->runFirestoreCommand('end-at-field-query-cursor');
        $this->assertContains('Document DC returned by end at population 1000000 field query cursor.', $output);
        $this->assertContains('Document SF returned by end at population 1000000 field query cursor.', $output);
    }

    /**
     * @depends testEndAtFieldQueryCursor
     */
    public function testStartAtSnapshotQueryCursor()
    {
        $output = $this->runFirestoreCommand('start-at-snapshot-query-cursor');
        $this->assertContains('Document SF returned by start at SF snapshot query cursor.', $output);
        $this->assertContains('Document TOK returned by start at SF snapshot query cursor.', $output);
        $this->assertContains('Document BJ returned by start at SF snapshot query cursor.', $output);
    }

    /**
     * @depends testStartAtSnapshotQueryCursor
     */
    public function testPaginatedQueryCursor()
    {
        $output = $this->runFirestoreCommand('paginated-query-cursor');
        $this->assertContains('Document BJ returned by paginated query cursor.', $output);
    }

    /**
     * @depends testPaginatedQueryCursor
     */
    public function testMultipleCursorConditions()
    {
        $output = $this->runFirestoreCommand('multiple-cursor-conditions');
    }

    public function testDeleteTestCollections()
    {
        $output = $this->runFirestoreCommand('delete-test-collections');
    }

    private function runFirestoreCommand($commandName)
    {
        return $this->runCommand($commandName, [
            'project' => self::$firestoreProjectId
        ]);
    }

    public function testDistributedCounter()
    {
        $this->runFirestoreCommand('initialize-distributed-counter');
        $outputZero = $this->runFirestoreCommand('get-distributed-counter-value');
        $this->assertContains('0', $outputZero);

        //check count of shards
        $db = new FirestoreClient([
            'projectId' => self::$firestoreProjectId,
        ]);
        $ref = $db->collection('Shards_collection')->document('Distributed_counters');
        $collect = $ref->collection('SHARDS');
        $docCollection = $collect->documents();

        $docIdList = [];
        foreach ($docCollection as $docSnap) {
            $docIdList[] = $docSnap->id();
        }
        $this->assertEquals(10, count($docIdList));

        //call thrice and check the value
        $this->runFirestoreCommand('update-distributed-counter');
        $this->runFirestoreCommand('update-distributed-counter');
        $this->runFirestoreCommand('update-distributed-counter');

        $output = $this->runFirestoreCommand('get-distributed-counter-value');
        $this->assertContains('3', $output);

        //remove temporary data
        foreach ($docIdList as $docId) {
            $collect->document($docId)->delete();
        }
    }
}
