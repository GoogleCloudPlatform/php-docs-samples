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
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for Firestore commands.
 */
class firestoreTest extends TestCase
{
    use TestTrait;

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
        } catch (\Exception $e) {
        }
    }

    public static function tearDownAfterClass(): void
    {
        foreach (self::$firestoreClient->document('samples/php')->collections() as $ref) {
            foreach ($ref->documents() as $doc) {
                foreach ($doc->reference()->collections() as $c) {
                    self::runFirestoreSnippet('data_delete_collection', [
                        self::$firestoreProjectId,
                        $c->name(),
                        1,
                    ]);
                }
            }

            self::runFirestoreSnippet('data_delete_collection', [
                self::$firestoreProjectId,
                $ref->name(),
                2,
            ]);
        }

        self::$firestoreClient->collection('samples')->document('php')->delete();
    }

    public function testInitialize()
    {
        $output = self::runFunctionSnippet('setup_client_create', [self::$projectId]);
        $this->assertStringContainsString('Created Cloud Firestore client with project ID: ', $output);
    }

    public function testInitializeProjectId()
    {
        # The lack of a second parameter implies that a non-empty projectId is
        # supplied to the snippet's function.
        $output = $this->runFirestoreSnippet('setup_client_create', [self::$projectId]);
        $this->assertStringContainsString('Created Cloud Firestore client with project ID:', $output);
    }

    public function testAddData()
    {
        $output = $this->runFirestoreSnippet('setup_dataset');
        $this->assertStringContainsString('Added data to the lovelace document in the users collection.', $output);
        $this->assertStringContainsString('Added data to the aturing document in the users collection.', $output);
    }

    /**
     * @depends testAddData
     */
    public function testRetrieveAllDocuments()
    {
        $output = $this->runFirestoreSnippet('setup_dataset_read');
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
        $output = $this->runFirestoreSnippet('data_set_from_map');
        $this->assertStringContainsString('Set data for the LA document in the cities collection.', $output);
    }

    /**
     * @depends testAddData
     */
    public function testAddDocDataTypes()
    {
        $output = $this->runFirestoreSnippet('data_set_from_map_nested');
        $this->assertStringContainsString('Set multiple data-type data for the one document in the data collection.', $output);
    }

    /**
     * @depends testAddData
     */
    public function testSetRequiresId()
    {
        $output = $this->runFirestoreSnippet('data_set_id_specified');
        $this->assertStringContainsString('Added document with ID: new-city-id', $output);
    }

    /**
     * @depends testAddData
     */
    public function testAddDocDataWithAutoId()
    {
        $output = $this->runFirestoreSnippet('data_set_id_random_collection');
        $this->assertStringContainsString('Added document with ID:', $output);
    }

    /**
     * @depends testAddData
     */
    public function testAddDocDataAfterAutoId()
    {
        $output = $this->runFirestoreSnippet('data_set_id_random_document_ref');
        $this->assertStringContainsString('Added document with ID:', $output);
    }

    public function testQueryCreateExamples()
    {
        $output = $this->runFirestoreSnippet('query_filter_dataset');
        $this->assertStringContainsString('Added example cities data to the cities collection.', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testCreateQueryState()
    {
        $output = $this->runFirestoreSnippet('query_filter_eq_string');
        $this->assertStringContainsString('Document SF returned by query state=CA', $output);
        $this->assertStringContainsString('Document LA returned by query state=CA', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testCreateQueryCapital()
    {
        $output = $this->runFirestoreSnippet('query_filter_eq_boolean');
        $this->assertStringContainsString('Document BJ returned by query capital=true', $output);
        $this->assertStringContainsString('Document DC returned by query capital=true', $output);
        $this->assertStringContainsString('Document TOK returned by query capital=true', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testSimpleQueries()
    {
        $output = $this->runFirestoreSnippet('query_filter_single_examples');
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
        $output = $this->runFirestoreSnippet('query_filter_array_contains');
        $this->assertStringContainsString('Document LA returned by query regions array-contains west_coast', $output);
        $this->assertStringContainsString('Document SF returned by query regions array-contains west_coast', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testArrayMembershipAny()
    {
        $output = $this->runFirestoreSnippet('query_filter_array_contains_any');
        $this->assertStringContainsString('Document DC returned by query regions array-contains-any [west_coast, east_coast]', $output);
        $this->assertStringContainsString('Document LA returned by query regions array-contains-any [west_coast, east_coast]', $output);
        $this->assertStringContainsString('Document SF returned by query regions array-contains-any [west_coast, east_coast]', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testInQuery()
    {
        $output = $this->runFirestoreSnippet('query_filter_in');
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
        $output = $this->runFirestoreSnippet('query_filter_in_with_array');
        $this->assertStringContainsString('Document DC returned by query regions in [[west_coast], [east_coast]]', $output);
        $this->assertStringNotContainsString('Document SF', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testNotEqQuery()
    {
        $output = $this->runFirestoreSnippet('query_filter_not_eq');
        $this->assertStringContainsString('Document BJ returned by query state!=false.', $output);
        $this->assertStringContainsString('Document TOK returned by query state!=false.', $output);
        $this->assertStringContainsString('Document DC returned by query state!=false.', $output);
        $this->assertStringNotContainsString('Document LA returned by query state!=false.', $output);
        $this->assertStringNotContainsString('Document SF returned by query state!=false.', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testNotInQuery()
    {
        $output = $this->runFirestoreSnippet('query_filter_not_in');
        $this->assertStringContainsString('Document BJ returned by query not_in ["USA","Japan"].', $output);
        $this->assertStringNotContainsString('Document SF returned by query not_in ["USA","Japan"].', $output);
        $this->assertStringNotContainsString('Document LA returned by query not_in ["USA","Japan"].', $output);
        $this->assertStringNotContainsString('Document DC returned by query not_in ["USA","Japan"].', $output);
        $this->assertStringNotContainsString('Document TOK returned by query not_in ["USA","Japan"].', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testChainedQuery()
    {
        $output = $this->runFirestoreSnippet('query_filter_compound_multi_eq');
        $this->assertStringContainsString('Document SF returned by query state=CA and name=San Francisco', $output);
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testCompositeIndexChainedQuery()
    {
        try {
            $output = $this->runFirestoreSnippet('query_filter_compound_multi_eq_lt');
            $this->assertStringContainsString('Document SF returned by query state=CA and population<1000000', $output);
        } catch (FailedPreconditionException $e) {
            $this->markTestSkipped('test requires manual creation of index. message: ' . $e->getMessage());
        }
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testRangeQuery()
    {
        $output = $this->runFirestoreSnippet('query_filter_range_valid');
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
        $this->runFirestoreSnippet('query_filter_range_invalid');
    }

    /**
     * @depends testQueryCreateExamples
     */
    public function testCollectionGroupQuerySetup()
    {
        try {
            $output = $this->runFirestoreSnippet('query_collection_group_dataset');
            $this->assertStringContainsString('Added example landmarks collections to the cities collection.', $output);
        } catch (FailedPreconditionException $e) {
            $this->markTestSkipped('test requires manual creation of index. message: ' . $e->getMessage());
        }
    }

    /**
     * @depends testCollectionGroupQuerySetup
     */
    public function testCollectionGroupQuery()
    {
        $output = $this->runFirestoreSnippet('query_collection_group_dataset');
        $this->assertStringContainsString('Added example landmarks collections to the cities collection.', $output);
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
        $output = $this->runFirestoreSnippet('data_delete_doc');
        $this->assertStringContainsString('Deleted the DC document in the cities collection.', $output);
    }

    /**
     * @depends testDeleteDocument
     */
    public function testDeleteField()
    {
        $output = $this->runFirestoreSnippet('data_delete_field');
        $this->assertStringContainsString('Deleted the capital field from the BJ document in the cities collection.', $output);
    }

    /**
     * @depends testDeleteField
     */
    public function testDeleteCollection()
    {
        $col = self::$firestoreClient->collection('samples/php/cities');
        $output = $this->runFirestoreSnippet('data_delete_collection', [
            self::$projectId,
            $col->name(),
            2,
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
        $output = $this->runFirestoreSnippet('data_get_dataset');
        $this->assertStringContainsString('Added example cities data to the cities collection.', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testGetCustomType()
    {
        $output = $this->runFirestoreSnippet('data_get_as_custom_type');
        $this->assertStringContainsString('Document data:', $output);
        $this->assertStringContainsString('Custom Type data', $output);
        $this->assertStringContainsString('[name] => San Francisco', $output);
        $this->assertStringContainsString('[state] => CA', $output);
        $this->assertStringContainsString('[country] => USA', $output);
        $this->assertStringContainsString('[capital] => false', $output);
        $this->assertStringContainsString('[population] => 860000', $output);
        $this->assertStringContainsString('[regions] =>', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testGetDocument()
    {
        $output = $this->runFirestoreSnippet('data_get_as_map');
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
        $output = $this->runFirestoreSnippet('data_query');
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
        $output = $this->runFirestoreSnippet('data_get_all_documents');
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

        $output = $this->runFirestoreSnippet('data_get_sub_collections');
        $this->assertStringContainsString('Found subcollection with id: neighborhoods', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testOrderByNameLimitQuery()
    {
        $output = $this->runFirestoreSnippet('query_order_limit');
        $this->assertStringContainsString('Document BJ returned by order by name with limit query', $output);
        $this->assertStringContainsString('Document LA returned by order by name with limit query', $output);
        $this->assertStringContainsString('Document SF returned by order by name with limit query', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testOrderByNameDescLimitQuery()
    {
        $output = $this->runFirestoreSnippet('query_order_desc_limit');
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
            $output = $this->runFirestoreSnippet('query_order_multi');
            $this->assertStringContainsString('Document LA returned by order by state and descending population query', $output);
            $this->assertStringContainsString('Document SF returned by order by state and descending population query', $output);
            $this->assertStringContainsString('Document BJ returned by order by state and descending population query', $output);
            $this->assertStringContainsString('Document DC returned by order by state and descending population query', $output);
            $this->assertStringContainsString('Document TOK returned by order by state and descending population query', $output);
        } catch (FailedPreconditionException $e) {
            $this->markTestSkipped('test requires manual creation of index. message: ' . $e->getMessage());
        }
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testWhereOrderByLimitQuery()
    {
        $output = $this->runFirestoreSnippet('query_order_limit_field_valid');
        $this->assertStringContainsString('Document LA returned by where order by limit query', $output);
        $this->assertStringContainsString('Document TOK returned by where order by limit query', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testRangeOrderByQuery()
    {
        $output = $this->runFirestoreSnippet('query_order_with_filter');
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
        $this->runFirestoreSnippet('query_order_field_invalid');
    }

    public function testDocumentRef()
    {
        $output = $this->runFirestoreSnippet('data_reference_document');
        $this->assertStringContainsString('Retrieved document: ', $output);
    }

    public function testCollectionRef()
    {
        $output = $this->runFirestoreSnippet('data_reference_collection');
        $this->assertStringContainsString('Retrieved collection: ', $output);
    }

    public function testDocumentPathRef()
    {
        $output = $this->runFirestoreSnippet('data_reference_document_path');
        $this->assertStringContainsString('Retrieved document from path: ', $output);
    }

    public function testSubcollectionRef()
    {
        $output = $this->runFirestoreSnippet('data_reference_subcollection');
        $this->assertStringContainsString('Retrieved document from subcollection: ', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testUpdateDoc()
    {
        $output = $this->runFirestoreSnippet('data_set_field');
        $this->assertStringContainsString('Updated the capital field of the DC document in the cities collection.', $output);
    }

    /**
     * @depends testRetrieveCreateExamples
     */
    public function testUpdateDocArray()
    {
        $output = $this->runFirestoreSnippet('data_set_array_operations');
        $this->assertStringContainsString('Updated the regions field of the DC document in the cities collection.', $output);
    }

    /**
     * @depends testUpdateDoc
     */
    public function testSetDocumentMerge()
    {
        $output = $this->runFirestoreSnippet('data_set_doc_upsert');
        $this->assertStringContainsString('Set document data by merging it into the existing BJ document in the cities collection.', $output);
    }

    /**
     * @depends testSetDocumentMerge
     */
    public function testUpdateNestedFields()
    {
        $output = $this->runFirestoreSnippet('data_set_nested_fields');
        $this->assertStringContainsString('Updated the age and favorite color fields of the frank document in the users collection.', $output);
    }

    /**
     * @depends testUpdateNestedFields
     */
    public function testUpdateServerTimestamp()
    {
        $output = $this->runFirestoreSnippet('data_set_server_timestamp');
        $this->assertStringContainsString('Updated the timestamp field of the some-id document in the objects collection.', $output);
    }

    /**
     * @depends testUpdateServerTimestamp
     */
    public function testRunSimpleTransaction()
    {
        $output = $this->runFirestoreSnippet('transaction_document_update');
        $this->assertStringContainsString('Ran a simple transaction to update the population field in the SF document in the cities collection.', $output);
    }

    /**
     * @depends testRunSimpleTransaction
     */
    public function testReturnInfoTransaction()
    {
        $output = $this->runFirestoreSnippet('transaction_document_update_conditional');
        $this->assertStringContainsString('Population updated successfully.', $output);
    }

    /**
     * @depends testReturnInfoTransaction
     */
    public function testBatchWrite()
    {
        $output = $this->runFirestoreSnippet('data_batch_writes');
        $this->assertStringContainsString('Batch write successfully completed.', $output);
    }

    /**
     * @depends testBatchWrite
     */
    public function testStartAtFieldQueryCursor()
    {
        $output = $this->runFirestoreSnippet('query_cursor_start_at_field_value_single');
        $this->assertStringContainsString('Document SF returned by start at population 1000000 field query cursor.', $output);
        $this->assertStringContainsString('Document TOK returned by start at population 1000000 field query cursor.', $output);
        $this->assertStringContainsString('Document BJ returned by start at population 1000000 field query cursor.', $output);
    }

    /**
     * @depends testStartAtFieldQueryCursor
     */
    public function testEndAtFieldQueryCursor()
    {
        $output = $this->runFirestoreSnippet('query_cursor_end_at_field_value_single');
        $this->assertStringContainsString('Document DC returned by end at population 1000000 field query cursor.', $output);
        $this->assertStringContainsString('Document SF returned by end at population 1000000 field query cursor.', $output);
    }

    /**
     * @depends testEndAtFieldQueryCursor
     */
    public function testStartAtSnapshotQueryCursor()
    {
        $output = $this->runFirestoreSnippet('query_cursor_start_at_document');
        $this->assertStringContainsString('Document SF returned by start at SF snapshot query cursor.', $output);
        $this->assertStringContainsString('Document TOK returned by start at SF snapshot query cursor.', $output);
        $this->assertStringContainsString('Document BJ returned by start at SF snapshot query cursor.', $output);
    }

    /**
     * @depends testStartAtSnapshotQueryCursor
     */
    public function testPaginatedQueryCursor()
    {
        $output = $this->runFirestoreSnippet('query_cursor_pagination');
        $this->assertStringContainsString('Document BJ returned by paginated query cursor.', $output);
    }

    /**
     * @depends testPaginatedQueryCursor
     */
    public function testMultipleCursorConditions()
    {
        try {
            $output = $this->runFirestoreSnippet('query_cursor_start_at_field_value_multi');
            $this->assertStringContainsString('Document TOK returned by start at ', $output);
        } catch (FailedPreconditionException $e) {
            $this->markTestSkipped('test requires manual creation of index. message: ' . $e->getMessage());
        }
    }

    public function testDistributedCounter()
    {
        $this->runFirestoreSnippet('solution_sharded_counter_create');
        $outputZero = $this->runFirestoreSnippet('solution_sharded_counter_get');
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
        $this->runFirestoreSnippet('solution_sharded_counter_increment');
        $this->runFirestoreSnippet('solution_sharded_counter_increment');
        $this->runFirestoreSnippet('solution_sharded_counter_increment');

        $output = $this->runFirestoreSnippet('solution_sharded_counter_get');
        $this->assertStringContainsString('3', $output);

        //remove temporary data
        foreach ($docIdList as $docId) {
            $collect->document($docId)->delete();
        }
    }

    private static function runFirestoreSnippet($snippetName, array $args = null)
    {
        if ($args === null) {
            $args = [
                self::$firestoreProjectId,
            ];
        }

        return self::runFunctionSnippet($snippetName, $args);
    }
}
