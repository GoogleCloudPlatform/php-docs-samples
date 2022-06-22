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

use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Instance;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnitRetry\RetryTrait;
use PHPUnit\Framework\TestCase;

/**
 * @retryAttempts 3
 * @retryDelayMethod exponentialBackoff
 */
class spannerTest extends TestCase
{
    use TestTrait {
        TestTrait::runFunctionSnippet as traitRunFunctionSnippet;
    }

    use RetryTrait, EventuallyConsistentTestTrait;

    /** @var string instanceId */
    protected static $instanceId;

    /** @var string lowCostInstanceId */
    protected static $lowCostInstanceId;

    /** @var string databaseId */
    protected static $databaseId;

    /** @var string encryptedDatabaseId */
    protected static $encryptedDatabaseId;

    /** @var string backupId */
    protected static $backupId;

    /** @var Instance $instance */
    protected static $instance;

    /** @var string multiInstanceId */
    protected static $multiInstanceId;

    /** @var Instance $multiInstance */
    protected static $multiInstance;

    /** @var string multiDatabaseId */
    protected static $multiDatabaseId;

    /** @var string instanceConfig */
    protected static $instanceConfig;

    /** @var string defaultLeader */
    protected static $defaultLeader;

    /** @var string defaultLeader */
    protected static $updatedDefaultLeader;

    /** @var string kmsKeyName */
    protected static $kmsKeyName;

    /**
     * Low cost instance with less than 1000 processing units.
     *
     * @var $instance lowCostInstance
     */
    protected static $lowCostInstance;

    /** @var $lastUpdateData int */
    protected static $lastUpdateDataTimestamp;

    public static function setUpBeforeClass(): void
    {
        self::checkProjectEnvVars();

        if (!extension_loaded('grpc')) {
            self::markTestSkipped('Must enable grpc extension.');
        }

        $spanner = new SpannerClient([
            'projectId' => self::$projectId,
        ]);

        self::$instanceId = 'test-' . time() . rand();
        self::$lowCostInstanceId = 'test-' . time() . rand();
        self::$databaseId = 'test-' . time() . rand();
        self::$encryptedDatabaseId = 'en-test-' . time() . rand();
        self::$backupId = 'backup-' . self::$databaseId;
        self::$instance = $spanner->instance(self::$instanceId);
        self::$kmsKeyName =
            'projects/' . self::$projectId . '/locations/us-central1/keyRings/spanner-test-keyring/cryptoKeys/spanner-test-cmek';
        self::$lowCostInstance = $spanner->instance(self::$lowCostInstanceId);

        self::$multiInstanceId = 'kokoro-multi-instance';
        self::$multiDatabaseId = 'test-' . time() . rand() . 'm';
        self::$instanceConfig = 'nam3';
        self::$defaultLeader = 'us-central1';
        self::$updatedDefaultLeader = 'us-east4';
        self::$multiInstance = $spanner->instance(self::$multiInstanceId);
    }

    public function testCreateInstance()
    {
        $output = $this->runFunctionSnippet('create_instance', [
            'instance_id' => self::$instanceId
        ]);
        $this->assertStringContainsString('Waiting for operation to complete...', $output);
        $this->assertStringContainsString('Created instance test-', $output);
    }

    public function testCreateInstanceWithProcessingUnits()
    {
        $output = $this->runFunctionSnippet('create_instance_with_processing_units', [
            'instance_id' => self::$lowCostInstanceId
        ]);
        $this->assertStringContainsString('Waiting for operation to complete...', $output);
        $this->assertStringContainsString('Created instance test-', $output);
    }

    /**
     * @depends testCreateInstance
     */
    public function testCreateDatabase()
    {
        $output = $this->runFunctionSnippet('create_database');
        $this->assertStringContainsString('Waiting for operation to complete...', $output);
        $this->assertStringContainsString('Created database test-', $output);
    }

    /**
     * @depends testCreateInstance
     */
    public function testCreateDatabaseWithEncryptionKey()
    {
        $output = $this->runFunctionSnippet('create_database_with_encryption_key', [
            self::$instanceId,
            self::$encryptedDatabaseId,
            self::$kmsKeyName,
        ]);
        $this->assertStringContainsString('Waiting for operation to complete...', $output);
        $this->assertStringContainsString('Created database en-test-', $output);
    }

    /**
     * @depends testCreateDatabase
     */
    public function testInsertData()
    {
        $output = $this->runFunctionSnippet('insert_data');
        $this->assertEquals('Inserted data.' . PHP_EOL, $output);
    }

    /**
     * @depends testInsertData
     */
    public function testQueryData()
    {
        $output = $this->runFunctionSnippet('query_data');
        $this->assertStringContainsString('SingerId: 1, AlbumId: 1, AlbumTitle: Total Junk', $output);
        $this->assertStringContainsString('SingerId: 1, AlbumId: 2, AlbumTitle: Go, Go, Go', $output);
        $this->assertStringContainsString('SingerId: 2, AlbumId: 1, AlbumTitle: Green', $output);
        $this->assertStringContainsString('SingerId: 2, AlbumId: 2, AlbumTitle: Forever Hold Your Peace', $output);
        $this->assertStringContainsString('SingerId: 2, AlbumId: 3, AlbumTitle: Terrified', $output);
    }

    /**
     * @depends testInsertData
     */
    public function testBatchQueryData()
    {
        $output = $this->runFunctionSnippet('batch_query_data');
        $this->assertStringContainsString('SingerId: 1, FirstName: Marc, LastName: Richards', $output);
        $this->assertStringContainsString('SingerId: 2, FirstName: Catalina, LastName: Smith', $output);
        $this->assertStringContainsString('SingerId: 3, FirstName: Alice, LastName: Trentor', $output);
        $this->assertStringContainsString('SingerId: 4, FirstName: Lea, LastName: Martin', $output);
        $this->assertStringContainsString('SingerId: 5, FirstName: David, LastName: Lomond', $output);
        $this->assertStringContainsString('Total Partitions:', $output);
        $this->assertStringContainsString('Total Records: 5', $output);
        $this->assertStringContainsString('Average Records Per Partition:', $output);
    }

    /**
     * @depends testInsertData
     */
    public function testReadData()
    {
        $output = $this->runFunctionSnippet('read_data');
        $this->assertStringContainsString('SingerId: 1, AlbumId: 1, AlbumTitle: Total Junk', $output);
        $this->assertStringContainsString('SingerId: 1, AlbumId: 2, AlbumTitle: Go, Go, Go', $output);
        $this->assertStringContainsString('SingerId: 2, AlbumId: 1, AlbumTitle: Green', $output);
        $this->assertStringContainsString('SingerId: 2, AlbumId: 2, AlbumTitle: Forever Hold Your Peace', $output);
        $this->assertStringContainsString('SingerId: 2, AlbumId: 3, AlbumTitle: Terrified', $output);
    }

    /**
     * @depends testInsertData
     */
    public function testDeleteData()
    {
        $output = $this->runFunctionSnippet('delete_data');
        $this->assertStringContainsString('Deleted data.' . PHP_EOL, $output);

        $spanner = new SpannerClient();
        $instance = $spanner->instance(spannerTest::$instanceId);
        $database = $instance->database(spannerTest::$databaseId);

        $results = $database->execute(
            'SELECT SingerId FROM Albums UNION ALL SELECT SingerId FROM Singers'
        );

        foreach ($results as $row) {
            $this->fail('Not all data was deleted.');
        }

        $output = $this->runFunctionSnippet('insert_data');
        $this->assertEquals('Inserted data.' . PHP_EOL, $output);
    }

    /**
     * @depends testDeleteData
     */
    public function testAddColumn()
    {
        $output = $this->runFunctionSnippet('add_column');
        $this->assertStringContainsString('Waiting for operation to complete...', $output);
        $this->assertStringContainsString('Added the MarketingBudget column.', $output);
    }

    /**
     * @depends testAddColumn
     */
    public function testUpdateData()
    {
        $output = $this->runFunctionSnippet('update_data');
        self::$lastUpdateDataTimestamp = time();
        $this->assertEquals('Updated data.' . PHP_EOL, $output);
    }

    /**
     * @depends testAddColumn
     */
    public function testQueryDataWithNewColumn()
    {
        $output = $this->runFunctionSnippet('query_data_with_new_column');
        $this->assertStringContainsString('SingerId: 1, AlbumId: 1, MarketingBudget:', $output);
        $this->assertStringContainsString('SingerId: 1, AlbumId: 2, MarketingBudget:', $output);
        $this->assertStringContainsString('SingerId: 2, AlbumId: 1, MarketingBudget:', $output);
        $this->assertStringContainsString('SingerId: 2, AlbumId: 2, MarketingBudget:', $output);
        $this->assertStringContainsString('SingerId: 2, AlbumId: 3, MarketingBudget:', $output);
    }

    /**
     * @depends testUpdateData
     */
    public function testReadWriteTransaction()
    {
        $this->runFunctionSnippet('update_data');
        $output = $this->runFunctionSnippet('read_write_transaction');
        $this->assertStringContainsString('Setting first album\'s budget to 300000 and the second album\'s budget to 300000', $output);
        $this->assertStringContainsString('Transaction complete.', $output);
    }

    /**
     * @depends testAddColumn
     */
    public function testCreateIndex()
    {
        $output = $this->runFunctionSnippet('create_index');
        $this->assertStringContainsString('Waiting for operation to complete...', $output);
        $this->assertStringContainsString('Added the AlbumsByAlbumTitle index.', $output);
    }

    /**
     * @depends testCreateIndex
     */
    public function testQueryDataWithIndex()
    {
        $output = $this->runFunctionSnippet('query_data_with_index');
        $this->assertStringContainsString('AlbumId: 2, AlbumTitle: Forever Hold Your Peace', $output);
        $this->assertStringContainsString('AlbumId: 2, AlbumTitle: Go, Go, Go', $output);
    }

    /**
     * @depends testCreateIndex
     */
    public function testReadDataWithIndex()
    {
        $output = $this->runFunctionSnippet('read_data_with_index');

        $this->assertStringContainsString('AlbumId: 1, AlbumTitle: Total Junk', $output);
        $this->assertStringContainsString('AlbumId: 2, AlbumTitle: Go, Go, Go', $output);
        $this->assertStringContainsString('AlbumId: 1, AlbumTitle: Green', $output);
        $this->assertStringContainsString('AlbumId: 3, AlbumTitle: Terrified', $output);
        $this->assertStringContainsString('AlbumId: 2, AlbumTitle: Forever Hold Your Peace', $output);
    }

    /**
     * @depends testCreateIndex
     */
    public function testCreateStoringIndex()
    {
        $output = $this->runFunctionSnippet('create_storing_index');
        $this->assertStringContainsString('Waiting for operation to complete...', $output);
        $this->assertStringContainsString('Added the AlbumsByAlbumTitle2 index.', $output);
    }

    /**
     * @depends testCreateStoringIndex
     */
    public function testReadDataWithStoringIndex()
    {
        $output = $this->runFunctionSnippet('read_data_with_storing_index');
        $this->assertStringContainsString('AlbumId: 2, AlbumTitle: Forever Hold Your Peace, MarketingBudget:', $output);
        $this->assertStringContainsString('AlbumId: 2, AlbumTitle: Go, Go, Go, MarketingBudget:', $output);
        $this->assertStringContainsString('AlbumId: 1, AlbumTitle: Green, MarketingBudget:', $output);
        $this->assertStringContainsString('AlbumId: 3, AlbumTitle: Terrified, MarketingBudget:', $output);
        $this->assertStringContainsString('AlbumId: 1, AlbumTitle: Total Junk, MarketingBudget:', $output);
    }

    /**
     * @depends testUpdateData
     */
    public function testReadOnlyTransaction()
    {
        $output = $this->runFunctionSnippet('read_only_transaction');
        $this->assertStringContainsString('SingerId: 1, AlbumId: 1, AlbumTitle: Total Junk', $output);
        $this->assertStringContainsString('SingerId: 1, AlbumId: 2, AlbumTitle: Go, Go, Go', $output);
        $this->assertStringContainsString('SingerId: 2, AlbumId: 1, AlbumTitle: Green', $output);
        $this->assertStringContainsString('SingerId: 2, AlbumId: 2, AlbumTitle: Forever Hold Your Peace', $output);
        $this->assertStringContainsString('SingerId: 2, AlbumId: 3, AlbumTitle: Terrified', $output);
    }

    /**
     * @depends testUpdateData
     */
    public function testReadStaleData()
    {
        // read-stale-data reads data that is exactly 15 seconds old.  So, make sure 15 seconds
        // have elapsed since testUpdateData().
        $elapsed = time() - self::$lastUpdateDataTimestamp;
        if ($elapsed < 16) {
            sleep(16 - $elapsed);
        }
        $output = $this->runFunctionSnippet('read_stale_data');
        $this->assertStringContainsString('SingerId: 1, AlbumId: 1, AlbumTitle: Total Junk', $output);
        $this->assertStringContainsString('SingerId: 1, AlbumId: 2, AlbumTitle: Go, Go, Go', $output);
        $this->assertStringContainsString('SingerId: 2, AlbumId: 1, AlbumTitle: Green', $output);
        $this->assertStringContainsString('SingerId: 2, AlbumId: 2, AlbumTitle: Forever Hold Your Peace', $output);
        $this->assertStringContainsString('SingerId: 2, AlbumId: 3, AlbumTitle: Terrified', $output);
    }

    /**
     * @depends testReadStaleData
     */
    public function testCreateTableTimestamp()
    {
        $output = $this->runFunctionSnippet('create_table_with_timestamp_column');
        $this->assertStringContainsString('Waiting for operation to complete...', $output);
        $this->assertStringContainsString('Created Performances table in database test-', $output);
    }

    /**
     * @depends testCreateTableTimestamp
     */
    public function testInsertDataTimestamp()
    {
        $output = $this->runFunctionSnippet('insert_data_with_timestamp_column');
        $this->assertEquals('Inserted data.' . PHP_EOL, $output);
    }

    /**
     * @depends testInsertDataTimestamp
     */
    public function testAddTimestampColumn()
    {
        $output = $this->runFunctionSnippet('add_timestamp_column');
        $this->assertStringContainsString('Waiting for operation to complete...', $output);
        $this->assertStringContainsString('Added LastUpdateTime as a commit timestamp column in Albums table', $output);
    }

    /**
     * @depends testAddTimestampColumn
     */
    public function testUpdateDataTimestamp()
    {
        $output = $this->runFunctionSnippet('update_data_with_timestamp_column');
        $this->assertEquals('Updated data.' . PHP_EOL, $output);
    }

    /**
     * @depends testUpdateDataTimestamp
     */
    public function testQueryDataTimestamp()
    {
        $output = $this->runFunctionSnippet('query_data_with_timestamp_column');
        $this->assertStringContainsString('SingerId: 1, AlbumId: 1, MarketingBudget: 1000000, LastUpdateTime: 20', $output);
        $this->assertStringContainsString('SingerId: 2, AlbumId: 2, MarketingBudget: 750000, LastUpdateTime: 20', $output);
        $this->assertStringContainsString('SingerId: 1, AlbumId: 2, MarketingBudget: NULL, LastUpdateTime: NULL', $output);
        $this->assertStringContainsString('SingerId: 2, AlbumId: 1, MarketingBudget: NULL, LastUpdateTime: NULL', $output);
        $this->assertStringContainsString('SingerId: 2, AlbumId: 3, MarketingBudget: NULL, LastUpdateTime: NULL', $output);
    }

    /**
     * @depends testCreateDatabase
     */
    public function testInsertStructData()
    {
        $output = $this->runFunctionSnippet('insert_struct_data');
        $this->assertEquals('Inserted data.' . PHP_EOL, $output);
    }

    /**
     * @depends testInsertStructData
     */
    public function testQueryDataWithStruct()
    {
        $output = $this->runFunctionSnippet('query_data_with_struct');
        $this->assertStringContainsString('SingerId: 6', $output);
    }

    /**
     * @depends testInsertStructData
     */
    public function testQueryDataWithArrayOfStruct()
    {
        $output = $this->runFunctionSnippet('query_data_with_array_of_struct');
        $this->assertStringContainsString('SingerId: 6', $output);
        $this->assertStringContainsString('SingerId: 7', $output);
        $this->assertStringContainsString('SingerId: 8', $output);
    }

    /**
     * @depends testInsertStructData
     */
    public function testQueryDataWithStructField()
    {
        $output = $this->runFunctionSnippet('query_data_with_struct_field');
        $this->assertStringContainsString('SingerId: 6', $output);
    }

    /**
     * @depends testInsertStructData
     */
    public function testQueryDataWithNestedStructField()
    {
        $output = $this->runFunctionSnippet('query_data_with_nested_struct_field');
        $this->assertStringContainsString('SingerId: 6 SongName: Imagination', $output);
        $this->assertStringContainsString('SingerId: 9 SongName: Imagination', $output);
    }

    /**
     * @depends testCreateDatabase
     */
    public function testInsertDataWithDml()
    {
        $output = $this->runFunctionSnippet('insert_data_with_dml');
        $this->assertStringContainsString('Inserted 1 row(s)', $output);
    }

    /**
     * @depends testAddColumn
     */
    public function testUpdateDataWithDml()
    {
        $output = $this->runFunctionSnippet('update_data_with_dml');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('Updated 1 row(s)', $output);
    }

    /**
     * @depends testAddColumn
     */
    public function testDeleteDataWithDml()
    {
        $output = $this->runFunctionSnippet('delete_data_with_dml');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('Deleted 1 row(s)', $output);
    }

    /**
     * @depends testInsertData
     */
    public function testUpdateDataWithDmlTimestamp()
    {
        $output = $this->runFunctionSnippet('update_data_with_dml_timestamp');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('Updated 2 row(s)', $output);
    }

    /**
     * @depends testCreateDatabase
     */
    public function testWriteReadWithDml()
    {
        $output = $this->runFunctionSnippet('write_read_with_dml');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('Timothy Campbell', $output);
    }

    /**
     * @depends testCreateDatabase
     */
    public function testUpdateDataWithDmlStructs()
    {
        $output = $this->runFunctionSnippet('update_data_with_dml_structs');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('Updated 1 row(s)', $output);
    }

    /**
     * @depends testInsertData
     */
    public function testWriteDataWithDML()
    {
        $output = $this->runFunctionSnippet('write_data_with_dml');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('Inserted 4 row(s)', $output);
    }

    /**
     * @depends testWriteDataWithDML
     */
    public function testQueryDataWithParameter()
    {
        $output = $this->runFunctionSnippet('query_data_with_parameter');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('SingerId: 12, FirstName: Melissa, LastName: Garcia', $output);
    }

    /**
     * @depends testAddColumn
     */
    public function testUpdateDataWithDmlTransaction()
    {
        $output = $this->runFunctionSnippet('write_data_with_dml_transaction');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('Transaction complete', $output);
    }

    /**
     * @depends testAddColumn
     */
    public function testUpdateDataWithPartitionedDML()
    {
        $output = $this->runFunctionSnippet('update_data_with_partitioned_dml');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('Updated 3 row(s)', $output);
    }

    /**
     * @depends testAddColumn
     */
    public function testDeleteDataWithPartitionedDML()
    {
        $output = $this->runFunctionSnippet('delete_data_with_partitioned_dml');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('Deleted 5 row(s)', $output);
    }

    /**
     * @depends testAddColumn
     */
    public function testUpdateDataWithBatchDML()
    {
        $output = $this->runFunctionSnippet('update_data_with_batch_dml');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('Executed 2 SQL statements using Batch DML', $output);
    }

    /**
     * @depends testAddColumn
     */
    public function testGetCommitStats()
    {
        $output = $this->runFunctionSnippet('get_commit_stats');
        $this->assertStringContainsString('Updated data with 10 mutations.', $output);
    }

    /**
     * @depends testCreateDatabase
     */
    public function testCreateTableDatatypes()
    {
        $output = $this->runFunctionSnippet('create_table_with_datatypes');
        $this->assertStringContainsString('Waiting for operation to complete...', $output);
        $this->assertStringContainsString('Created Venues table in database test-', $output);
    }

    /**
     * @depends testCreateTableDatatypes
     */
    public function testInsertDataWithDatatypes()
    {
        $output = $this->runFunctionSnippet('insert_data_with_datatypes');
        $this->assertEquals('Inserted data.' . PHP_EOL, $output);
    }

    /**
     * @depends testInsertDataWithDatatypes
     */
    public function testQueryDataWithArrayParameter()
    {
        $output = $this->runFunctionSnippet('query_data_with_array_parameter');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('VenueId: 19, VenueName: Venue 19, AvailableDate: 2020-11-01', $output);
        $this->assertStringContainsString('VenueId: 42, VenueName: Venue 42, AvailableDate: 2020-10-01', $output);
    }

    /**
     * @depends testInsertDataWithDatatypes
     */
    public function testQueryDataWithBoolParameter()
    {
        $output = $this->runFunctionSnippet('query_data_with_bool_parameter');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('VenueId: 19, VenueName: Venue 19, OutdoorVenue: True', $output);
    }

    /**
     * @depends testInsertDataWithDatatypes
     */
    public function testQueryDataWithBytesParameter()
    {
        $output = $this->runFunctionSnippet('query_data_with_bytes_parameter');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('VenueId: 4, VenueName: Venue 4', $output);
    }

    /**
     * @depends testInsertDataWithDatatypes
     */
    public function testQueryDataWithDateParameter()
    {
        $output = $this->runFunctionSnippet('query_data_with_date_parameter');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('VenueId: 4, VenueName: Venue 4, LastContactDate: 2018-09-02', $output);
        $this->assertStringContainsString('VenueId: 42, VenueName: Venue 42, LastContactDate: 2018-10-01', $output);
    }

    /**
     * @depends testInsertDataWithDatatypes
     */
    public function testQueryDataWithFloatParameter()
    {
        $output = $this->runFunctionSnippet('query_data_with_float_parameter');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('VenueId: 4, VenueName: Venue 4, PopularityScore: 0.8', $output);
        $this->assertStringContainsString('VenueId: 19, VenueName: Venue 19, PopularityScore: 0.9', $output);
    }

    /**
     * @depends testInsertDataWithDatatypes
     */
    public function testQueryDataWithIntParameter()
    {
        $output = $this->runFunctionSnippet('query_data_with_int_parameter');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('VenueId: 19, VenueName: Venue 19, Capacity: 6300', $output);
        $this->assertStringContainsString('VenueId: 42, VenueName: Venue 42, Capacity: 3000', $output);
    }

    /**
     * @depends testInsertDataWithDatatypes
     */
    public function testQueryDataWithStringParameter()
    {
        $output = $this->runFunctionSnippet('query_data_with_string_parameter');
        self::$lastUpdateDataTimestamp = time();
        $this->assertStringContainsString('VenueId: 42, VenueName: Venue 42', $output);
    }

    /**
     * @depends testInsertDataWithDatatypes
     */
    public function testQueryDataWithTimestampParameter()
    {
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runFunctionSnippet('query_data_with_timestamp_parameter');
            self::$lastUpdateDataTimestamp = time();
            $this->assertStringContainsString('VenueId: 4, VenueName: Venue 4, LastUpdateTime:', $output);
            $this->assertStringContainsString('VenueId: 19, VenueName: Venue 19, LastUpdateTime:', $output);
            $this->assertStringContainsString('VenueId: 42, VenueName: Venue 42, LastUpdateTime:', $output);
        });
    }

    /**
     * @depends testInsertDataWithDatatypes
     */
    public function testQueryDataWithQueryOptions()
    {
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runFunctionSnippet('query_data_with_query_options');
            self::$lastUpdateDataTimestamp = time();
            $this->assertStringContainsString('VenueId: 4, VenueName: Venue 4, LastUpdateTime:', $output);
            $this->assertStringContainsString('VenueId: 19, VenueName: Venue 19, LastUpdateTime:', $output);
            $this->assertStringContainsString('VenueId: 42, VenueName: Venue 42, LastUpdateTime:', $output);
        });
    }

    /**
     * @depends testInsertDataWithDatatypes
     */
    public function testAddNumericColumn()
    {
        $output = $this->runFunctionSnippet('add_numeric_column');
        $this->assertStringContainsString('Waiting for operation to complete...', $output);
        $this->assertStringContainsString('Added Revenue as a NUMERIC column in Venues table', $output);
    }

    /**
     * @depends testAddNumericColumn
     */
    public function testUpdateDataNumeric()
    {
        $output = $this->runFunctionSnippet('update_data_with_numeric_column');
        $this->assertEquals('Updated data.' . PHP_EOL, $output);
    }

    /**
     * @depends testUpdateDataTimestamp
     */
    public function testQueryDataNumeric()
    {
        $output = $this->runFunctionSnippet('query_data_with_numeric_parameter');
        $this->assertStringContainsString('VenueId: 4, Revenue: 35000', $output);
    }

    /**
     * @depends testInsertDataWithDatatypes
     */
    public function testAddJsonColumn()
    {
        $output = $this->runFunctionSnippet('add_json_column');
        $this->assertStringContainsString('Waiting for operation to complete...', $output);
        $this->assertStringContainsString('Added VenueDetails as a JSON column in Venues table', $output);
    }

    /**
     * @depends testAddJsonColumn
     */
    public function testUpdateDataJson()
    {
        $output = $this->runFunctionSnippet('update_data_with_json_column');
        $this->assertEquals('Updated data.' . PHP_EOL, $output);
    }

    /**
     * @depends testUpdateDataJson
     */
    public function testQueryDataJson()
    {
        $output = $this->runFunctionSnippet('query_data_with_json_parameter');
        $this->assertStringContainsString('VenueId: 19, VenueDetails: ', $output);
    }

    /**
     * @depends testInsertDataWithDatatypes
     */
    public function testSetTransactionTag()
    {
        $output = $this->runFunctionSnippet('set_transaction_tag');
        $this->assertStringContainsString('Venue capacities updated.', $output);
        $this->assertStringContainsString('New venue inserted.', $output);
    }

    /**
     * @depends testInsertData
     */
    public function testSetRequestTag()
    {
        $output = $this->runFunctionSnippet('set_request_tag');
        $this->assertStringContainsString('SingerId: 1, AlbumId: 1, AlbumTitle: Total Junk', $output);
    }

    /**
     * @depends testInsertDataWithDatatypes
     */
    public function testCreateClientWithQueryOptions()
    {
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runFunctionSnippet('create_client_with_query_options');
            self::$lastUpdateDataTimestamp = time();
            $this->assertStringContainsString('VenueId: 4, VenueName: Venue 4, LastUpdateTime:', $output);
            $this->assertStringContainsString('VenueId: 19, VenueName: Venue 19, LastUpdateTime:', $output);
            $this->assertStringContainsString('VenueId: 42, VenueName: Venue 42, LastUpdateTime:', $output);
        });
    }

    private function testGetInstanceConfig()
    {
        $output = $this->runFunctionSnippet('get_instance_config', [
            'instance_config' => self::$instanceConfig
        ]);
        $this->assertStringContainsString(self::$instanceConfig, $output);
    }

    private function testListInstanceConfigs()
    {
        $output = $this->runFunctionSnippet('list_instance_configs');
        $this->assertStringContainsString(self::$instanceConfig, $output);
    }

    private function testCreateDatabaseWithDefaultLeader()
    {
        $output = $this->runFunctionSnippet('create_database_with_default_leader', [
            'instance_id' => self::$multiInstanceId,
            'database_id' => self::$multiDatabaseId,
            'defaultLeader' => self::$defaultLeader
        ]);
        $this->assertStringContainsString(self::$defaultLeader, $output);
    }

    /**
     * @depends testCreateDatabaseWithDefaultLeader
     */
    private function testQueryInformationSchemaDatabaseOptions()
    {
        $output = $this->runFunctionSnippet('query_information_schema_database_options', [
            'instance_id' => self::$multiInstanceId,
            'database_id' => self::$multiDatabaseId,
        ]);
        $this->assertStringContainsString(self::$defaultLeader, $output);
    }

    /**
     * @depends testCreateDatabaseWithDefaultLeader
     */
    private function testUpdateDatabaseWithDefaultLeader()
    {
        $output = $this->runFunctionSnippet('update_database_with_default_leader', [
            'instance_id' => self::$multiInstanceId,
            'database_id' => self::$multiDatabaseId,
            'defaultLeader' => self::$updatedDefaultLeader
        ]);
        $this->assertStringContainsString(self::$updatedDefaultLeader, $output);
    }

    /**
     * @depends testUpdateDatabaseWithDefaultLeader
     */
    private function testGetDatabaseDdl()
    {
        $output = $this->runFunctionSnippet('get_database_ddl', [
            'instance_id' => self::$multiInstanceId,
            'database_id' => self::$multiDatabaseId,
        ]);
        $this->assertStringContainsString(self::$multiDatabaseId, $output);
        $this->assertStringContainsString(self::$updatedDefaultLeader, $output);
    }

    /**
     * @depends testUpdateDatabaseWithDefaultLeader
     */
    private function testListDatabases()
    {
        $output = $this->runFunctionSnippet('list_databases');
        $this->assertStringContainsString(self::$databaseId, $output);
        $this->assertStringContainsString(self::$multiDatabaseId, $output);
        $this->assertStringContainsString(self::$updatedDefaultLeader, $output);
    }

    private function runFunctionSnippet($sampleName, $params = [])
    {
        return $this->traitRunFunctionSnippet(
            $sampleName,
            array_values($params) ?: [self::$instanceId, self::$databaseId]
        );
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$instance->exists()) {// Clean up database
            $database = self::$instance->database(self::$databaseId);
            $database->drop();
        }
        $database = self::$multiInstance->database(self::$databaseId);
        $database->drop();
        self::$instance->delete();
        self::$lowCostInstance->delete();
    }
}
