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

use Google\Cloud\Core\Exception\BadRequestException;
use Google\Cloud\Core\Exception\FailedPreconditionException;
use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use Google\Cloud\TestUtils\TestTrait;
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
    private static $firestoreClient;

    public static function setUpBeforeClass(): void
    {
        if (!extension_loaded('grpc')) {
            self::markTestSkipped('Must enable grpc extension.');
        }
        self::$firestoreProjectId = self::requireEnv('FIRESTORE_PROJECT_ID');
        self::$firestoreClient = new FirestoreClient([
            'projectId' => self::$firestoreProjectId,
        ]);

        try {
            self::$firestoreClient->collection('samples')->document('php')->create();
        } catch (\Exception $e) {}
    }

    public static function tearDownAfterClass(): void
    {
        foreach (self::$firestoreClient->document('samples/php')->collections() as $ref) {
            self::runFirestoreSnippet('delete_collection', [
                'collectionReference' => $ref,
                'batchSize' => 2,
            ]);
        }

        self::$firestoreClient->collection('samples')->document('php')->delete();
    }

    public function testInitialize()
    {
        $output = $this->runFirestoreSnippet('initialize', []);
        $this->assertStringContainsString('Created Cloud Firestore client with default project ID.', $output);
    }

    public function testInitializeProjectId()
    {
        $output = $this->runFirestoreSnippet('initialize_project_id');
        $this->assertStringContainsString('Created Cloud Firestore client with project ID:', $output);
    }

    public function testAddData()
    {
        $output = $this->runFirestoreSnippet('add_data');
        $this->assertStringContainsString('Added data to the lovelace document in the users collection.', $output);
        $this->assertStringContainsString('Added data to the aturing document in the users collection.', $output);
    }

    /**
     * @depends testAddData
     */
    public function testRetrieveAllDocuments()
    {
        $output = $this->runFirestoreSnippet('get_all');
        $this->assertStringContainsString('User:', $output);
        $this->assertStringContainsString('First: Ada', $output);
        $this->assertStringContainsString('Last: Lovelace', $output);
        $this->assertStringContainsString('Born: 1815', $output);
        $this->assertStringContainsString('First: Alan', $output);
        $this->assertStringContainsString('Middle: Mathison', $output);
        $this->assertStringContainsString('Last: Turing', $output);
        $this->assertStringContainsString('Born: 1912', $output);
        $this->assertStringContainsString('Retrieved and printed out all documents from the users collection.', $output);
    }

    /**
     * @depends testAddData
     */
    public function testSetDocument()
    {
        $output = $this->runFirestoreSnippet('set_document');
        $this->assertStringContainsString('Set data for the LA document in the cities collection.', $output);
    }

    /**
     * @depends testAddData
     */
    public function testAddDocDataTypes()
    {
        $output = $this->runFirestoreSnippet('add_doc_data_types');
        $this->assertStringContainsString('Set multiple data-type data for the one document in the data collection.', $output);
    }

    /**
     * @depends testAddData
     */
    public function testSetRequiresId()
    {
        $output = $this->runFirestoreSnippet('set_requires_id');
        $this->assertStringContainsString('Added document with ID: new-city-id', $output);
    }

    /**
     * @depends testAddData
     */
    public function testAddDocDataWithAutoId()
    {
        $output = $this->runFirestoreSnippet('add_doc_data_with_auto_id');
        $this->assertStringContainsString('Added document with ID:', $output);
    }

    /**
     * @depends testAddData
     */
    public function testAddDocDataAfterAutoId()
    {
        $output = $this->runFirestoreSnippet('add_doc_data_after_auto_id');
        $this->assertStringContainsString('Added document with ID:', $output);
    }

    public function testQueryCreateExamples()
    {
        $output = $this->runFirestoreSnippet('query_create_examples');
        $this->assertStringContainsString('Added example cities data to the cities collection.', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testCreateQueryState()
    {
        $output = $this->runFirestoreSnippet('create_query_state');
        $this->assertStringContainsString('Document SF returned by query state=CA', $output);
        $this->assertStringContainsString('Document LA returned by query state=CA', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testCreateQueryCapital()
    {
        $output = $this->runFirestoreSnippet('create_query_capital');
        $this->assertStringContainsString('Document BJ returned by query capital=true', $output);
        $this->assertStringContainsString('Document DC returned by query capital=true', $output);
        $this->assertStringContainsString('Document TOK returned by query capital=true', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testSimpleQueries()
    {
        $output = $this->runFirestoreSnippet('simple_queries');
        $this->assertStringContainsString('Document LA returned by query state=CA', $output);
        $this->assertStringContainsString('Document SF returned by query state=CA', $output);
        $this->assertStringContainsString('Document BJ returned by query population>1000000', $output);
        $this->assertStringContainsString('Document LA returned by query population>1000000', $output);
        $this->assertStringContainsString('Document TOK returned by query population>1000000', $output);
        $this->assertStringContainsString('Document SF returned by query name>=San Francisco', $output);
        $this->assertStringContainsString('Document TOK returned by query name>=San Francisco', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testArrayMembership()
    {
        $output = $this->runFirestoreSnippet('array_membership');
        $this->assertStringContainsString('Document LA returned by query regions array-contains west_coast', $output);
        $this->assertStringContainsString('Document SF returned by query regions array-contains west_coast', $output);
    }


    /**
     * @depends testQueryCreateExamples
     */
    public function testArrayMembershipAny()
    {
        $output = $this->runFirestoreSnippet('array_membership_any');
        $this->assertStringContainsString('Document DC returned by query regions array-contains-any [west_coast, east_coast]', $output);
        $this->assertStringContainsString('Document LA returned by query regions array-contains-any [west_coast, east_coast]', $output);
        $this->assertStringContainsString('Document SF returned by query regions array-contains-any [west_coast, east_coast]', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testInQuery()
    {
        $output = $this->runFirestoreSnippet('in_query');
        $this->assertStringContainsString('Document DC returned by query country in [USA, Japan]', $output);
        $this->assertStringContainsString('Document LA returned by query country in [USA, Japan]', $output);
        $this->assertStringContainsString('Document SF returned by query country in [USA, Japan]', $output);
        $this->assertStringContainsString('Document TOK returned by query country in [USA, Japan]', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testInArrayQuery()
    {
        $output = $this->runFirestoreSnippet('in_array_query');
        $this->assertStringContainsString('Document DC returned by query regions in [[west_coast], [east_coast]]', $output);
        $this->assertStringNotContainsString('Document SF', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testChainedQuery()
    {
        $output = $this->runFirestoreSnippet('chained_query');
        $this->assertStringContainsString('Document SF returned by query state=CA and name=San Francisco', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testCompositeIndexChainedQuery()
    {
        try {
            $output = $this->runFirestoreSnippet('composite_index_chained_query');
            $this->assertStringContainsString('Document SF returned by query state=CA and population<1000000', $output);
        } catch (FailedPreconditionException $e) {
            $this->markTestSkipped("test requires manual creation of index. message: " . $e->getMessage());
        }
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testRangeQuery()
    {
        $output = $this->runFirestoreSnippet('range_query');
        $this->assertStringContainsString('Document LA returned by query CA<=state<=IN', $output);
        $this->assertStringContainsString('Document SF returned by query CA<=state<=IN', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testInvalidRangeQuery()
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage(
            'Cannot have inequality filters on multiple properties'
        );
        $this->runFirestoreSnippet('invalid_range_query');
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testCollectionGroupQuerySetup()
    {
        try {
            $output = $this->runFirestoreSnippet('collection_group_query_setup');
            $this->assertStringContainsString('Added example landmarks collections to the cities collection.', $output);
        } catch (FailedPreconditionException $e) {
            $this->markTestSkipped("test requires manual creation of index. message: " . $e->getMessage());
        }
    }

    /**
     * @depends testCollectionGroupQuerySetup
     */
    public function testCollectionGroupQuery()
    {
        $output = $this->runFirestoreSnippet('collection_group_query');
        $this->assertStringContainsString('Beijing Ancient Observatory', $output);
        $this->assertStringContainsString('National Air and Space Museum', $output);
        $this->assertStringContainsString('The Getty', $output);
        $this->assertStringContainsString('Legion of Honor', $output);
        $this->assertStringContainsString('National Museum of Nature and Science', $output);
        $this->assertStringNotContainsString('Golden Gate Bridge', $output);
        $this->assertStringNotContainsString('Griffith Park', $output);
        $this->assertStringNotContainsString('Lincoln Memorial', $output);
        $this->assertStringNotContainsString('Ueno Park', $output);
        $this->assertStringNotContainsString('Jingshan Park', $output);
    }

    /**
     * @depends testArrayMembership
     * @depends testArrayMembershipAny
     * @depends testInQuery
     * @depends testInArrayQuery
     * @depends testCollectionGroupQuery
     */
    public function testDeleteDocument()
    {
        $output = $this->runFirestoreSnippet('delete_doc');
        $this->assertStringContainsString('Deleted the DC document in the cities collection.', $output);
    }

    /**
     * @depends testDeleteDocument
     */
    public function testDeleteField()
    {
        $output = $this->runFirestoreSnippet('delete_field');
        $this->assertStringContainsString('Deleted the capital field from the BJ document in the cities collection.', $output);
    }

    /**
     * @depends testDeleteField
     */
    public function testDeleteCollection()
    {
        $col = self::$firestoreClient->collection('samples/php/cities');
        $output = $this->runFirestoreSnippet('delete_collection', [
            'collectionReference' => $col,
            'batchSize' => 2,
        ]);

        $this->assertStringContainsString('Deleting document BJ', $output);
        $this->assertStringContainsString('Deleting document LA', $output);
        $this->assertStringContainsString('Deleting document TOK', $output);
        $this->assertStringContainsString('Deleting document SF', $output);
    }

    /**
     * @depends testDeleteField
     */
    public function testRetrieveCreateExamples()
    {
        $output = $this->runFirestoreSnippet('retrieve_create_examples');
        $this->assertStringContainsString('Added example cities data to the cities collection.', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testGetDocument()
    {
        $output = $this->runFirestoreSnippet('get_document');
        $this->assertStringContainsString('Document data:', $output);
        $this->assertStringContainsString('[population] => 860000', $output);
        $this->assertStringContainsString('[state] => CA', $output);
        $this->assertStringContainsString('[capital] =>', $output);
        $this->assertStringContainsString('[name] => San Francisco', $output);
        $this->assertStringContainsString('[country] => USA', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testGetMultipleDocs()
    {
        $output = $this->runFirestoreSnippet('get_multiple_docs');
        $this->assertStringContainsString('Document data for document DC:', $output);
        $this->assertStringContainsString('Document data for document TOK:', $output);
        $this->assertStringContainsString('[name] => Washington D.C.', $output);
        $this->assertStringContainsString('[name] => Tokyo', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testGetAllDocs()
    {
        $output = $this->runFirestoreSnippet('get_all_docs');
        $this->assertStringContainsString('Document data for document LA:', $output);
        $this->assertStringContainsString('[name] => Los Angeles', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testListSubcollections()
    {
        $cityRef = self::$firestoreClient->collection('samples/php/cities')->document('SF');
        $subcollectionRef = $cityRef->collection('neighborhoods');
        $data = [
            'name' => 'Marina',
        ];
        $subcollectionRef->document('Marina')->set($data);

        $output = $this->runFirestoreSnippet('list_subcollections');
        $this->assertStringContainsString('Found subcollection with id: neighborhoods', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testOrderByNameLimitQuery()
    {
        $output = $this->runFirestoreSnippet('order_by_name_limit_query');
        $this->assertStringContainsString('Document BJ returned by order by name with limit query', $output);
        $this->assertStringContainsString('Document LA returned by order by name with limit query', $output);
        $this->assertStringContainsString('Document SF returned by order by name with limit query', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testOrderByNameDescLimitQuery()
    {
        $output = $this->runFirestoreSnippet('order_by_name_desc_limit_query');
        $this->assertStringContainsString('Document DC returned by order by name descending with limit query', $output);
        $this->assertStringContainsString('Document TOK returned by order by name descending with limit query', $output);
        $this->assertStringContainsString('Document SF returned by order by name descending with limit query', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testOrderByStateAndPopulationQuery()
    {
        try {
            $output = $this->runFirestoreSnippet('order_by_state_and_population_query');
            $this->assertStringContainsString('Document LA returned by order by state and descending population query', $output);
            $this->assertStringContainsString('Document SF returned by order by state and descending population query', $output);
            $this->assertStringContainsString('Document BJ returned by order by state and descending population query', $output);
            $this->assertStringContainsString('Document DC returned by order by state and descending population query', $output);
            $this->assertStringContainsString('Document TOK returned by order by state and descending population query', $output);
        } catch (FailedPreconditionException $e) {
            $this->markTestSkipped("test requires manual creation of index. message: " . $e->getMessage());
        }
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testWhereOrderByLimitQuery()
    {
        $output = $this->runFirestoreSnippet('where_order_by_limit_query');
        $this->assertStringContainsString('Document LA returned by where order by limit query', $output);
        $this->assertStringContainsString('Document TOK returned by where order by limit query', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testRangeOrderByQuery()
    {
        $output = $this->runFirestoreSnippet('range_order_by_query');
        $this->assertStringContainsString('Document LA returned by range with order by query', $output);
        $this->assertStringContainsString('Document TOK returned by range with order by query', $output);
        $this->assertStringContainsString('Document BJ returned by range with order by query', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testInvalidRangeOrderByQuery()
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage(
            'inequality filter property and first sort order must be the same'
        );
        $this->runFirestoreSnippet('invalid_range_order_by_query');
    }

    public function testDocumentRef()
    {
        $output = $this->runFirestoreSnippet('document_ref');
        $this->assertStringContainsString('Retrieved document: ', $output);
    }

    public function testCollectionRef()
    {
        $output = $this->runFirestoreSnippet('collection_ref');
        $this->assertStringContainsString('Retrieved collection: ', $output);
    }

    public function testDocumentPathRef()
    {
        $output = $this->runFirestoreSnippet('document_path_ref');
        $this->assertStringContainsString('Retrieved document from path: ', $output);
    }

    public function testSubcollectionRef()
    {
        $output = $this->runFirestoreSnippet('subcollection_ref');
        $this->assertStringContainsString('Retrieved document from subcollection: ', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testUpdateDoc()
    {
        $output = $this->runFirestoreSnippet('update_doc');
        $this->assertStringContainsString('Updated the capital field of the DC document in the cities collection.', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testUpdateDocArray()
    {
        $output = $this->runFirestoreSnippet('update_doc_array');
        $this->assertStringContainsString('Updated the regions field of the DC document in the cities collection.', $output);
    }

    /**
     * @depends testUpdateDoc
     */
    public function testSetDocumentMerge()
    {
        $output = $this->runFirestoreSnippet('set_document_merge');
        $this->assertStringContainsString('Set document data by merging it into the existing BJ document in the cities collection.', $output);
    }

    /**
     * @depends testSetDocumentMerge
     */
    public function testUpdateNestedFields()
    {
        $output = $this->runFirestoreSnippet('update_nested_fields');
        $this->assertStringContainsString('Updated the age and favorite color fields of the frank document in the users collection.', $output);
    }

    /**
     * @depends testUpdateNestedFields
     */
    public function testUpdateServerTimestamp()
    {
        $output = $this->runFirestoreSnippet('update_server_timestamp');
        $this->assertStringContainsString('Updated the timestamp field of the some-id document in the objects collection.', $output);
    }

    /**
     * @depends testUpdateServerTimestamp
     */
    public function testRunSimpleTransaction()
    {
        $output = $this->runFirestoreSnippet('run_simple_transaction');
        $this->assertStringContainsString('Ran a simple transaction to update the population field in the SF document in the cities collection.', $output);
    }

    /**
     * @depends testRunSimpleTransaction
     */
    public function testReturnInfoTransaction()
    {
        $output = $this->runFirestoreSnippet('return_info_transaction');
        $this->assertStringContainsString('Population updated successfully.', $output);
    }

    /**
     * @depends testReturnInfoTransaction
     */
    public function testBatchWrite()
    {
        $output = $this->runFirestoreSnippet('batch_write');
        $this->assertStringContainsString('Batch write successfully completed.', $output);
    }

    /**
     * @depends testBatchWrite
     */
    public function testStartAtFieldQueryCursor()
    {
        $output = $this->runFirestoreSnippet('start_at_field_query_cursor');
        $this->assertStringContainsString('Document SF returned by start at population 1000000 field query cursor.', $output);
        $this->assertStringContainsString('Document TOK returned by start at population 1000000 field query cursor.', $output);
        $this->assertStringContainsString('Document BJ returned by start at population 1000000 field query cursor.', $output);
    }

    /**
     * @depends testStartAtFieldQueryCursor
     */
    public function testEndAtFieldQueryCursor()
    {
        $output = $this->runFirestoreSnippet('end_at_field_query_cursor');
        $this->assertStringContainsString('Document DC returned by end at population 1000000 field query cursor.', $output);
        $this->assertStringContainsString('Document SF returned by end at population 1000000 field query cursor.', $output);
    }

    /**
     * @depends testEndAtFieldQueryCursor
     */
    public function testStartAtSnapshotQueryCursor()
    {
        $output = $this->runFirestoreSnippet('start_at_snapshot_query_cursor');
        $this->assertStringContainsString('Document SF returned by start at SF snapshot query cursor.', $output);
        $this->assertStringContainsString('Document TOK returned by start at SF snapshot query cursor.', $output);
        $this->assertStringContainsString('Document BJ returned by start at SF snapshot query cursor.', $output);
    }

    /**
     * @depends testStartAtSnapshotQueryCursor
     */
    public function testPaginatedQueryCursor()
    {
        $output = $this->runFirestoreSnippet('paginated_query_cursor');
        $this->assertStringContainsString('Document BJ returned by paginated query cursor.', $output);
    }

    /**
     * @depends testPaginatedQueryCursor
     */
    public function testMultipleCursorConditions()
    {
        try {
            $output = $this->runFirestoreSnippet('multiple_cursor_conditions');
            $this->assertStringContainsString('Document TOK returned by start at ', $output);
        } catch (FailedPreconditionException $e) {
            $this->markTestSkipped("test requires manual creation of index. message: " . $e->getMessage());
        }
    }

    private static function runFirestoreSnippet($snippetName, array $args = null)
    {
        if ($args === null) {
            $args = [
                'projectId' => self::$firestoreProjectId
            ];
        }

        return self::runFunctionSnippet($snippetName, $args);
    }

    public function testDistributedCounter()
    {
        $this->runFirestoreSnippet('initialize_distributed_counter');
        $outputZero = $this->runFirestoreSnippet('get_distributed_counter_value');
        $this->assertStringContainsString('0', $outputZero);

        //check count of shards
        $db = new FirestoreClient([
            'projectId' => self::$firestoreProjectId,
        ]);
        $collect = $db->collection('samples/php/distributedCounters');
        $docCollection = $collect->documents();

        $docIdList = [];
        foreach ($docCollection as $docSnap) {
            $docIdList[] = $docSnap->id();
        }
        $this->assertEquals(10, count($docIdList));

        //call thrice and check the value
        $this->runFirestoreSnippet('update_distributed_counter');
        $this->runFirestoreSnippet('update_distributed_counter');
        $this->runFirestoreSnippet('update_distributed_counter');

        $output = $this->runFirestoreSnippet('get_distributed_counter_value');
        $this->assertStringContainsString('3', $output);

        //remove temporary data
        foreach ($docIdList as $docId) {
            $collect->document($docId)->delete();
        }
    }
}
