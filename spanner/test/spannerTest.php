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

use Google\Cloud\Spanner\Database;
use Google\Cloud\Spanner\SpannerClient;
use Google\Cloud\Spanner\Instance;
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnitRetry\RetryTrait;
use PHPUnit\Framework\TestCase;

class spannerTest extends TestCase
{
    use RetryTrait, TestTrait, EventuallyConsistentTestTrait;
    use ExecuteCommandTrait {
        ExecuteCommandTrait::runCommand as traitRunCommand;
    }

    private static $commandFile = __DIR__ . '/../spanner.php';

    /** @var string instanceId */
    protected static $instanceId;

    /** @var string databaseId */
    protected static $databaseId;

    /** @var string backupId */
    protected static $backupId;

    /** @var $instance Instance */
    protected static $instance;

    /** @var $lastUpdateData int */
    protected static $lastUpdateDataTimestamp;

    public static function setUpBeforeClass()
    {
        self::checkProjectEnvVars();

        if (!extension_loaded('grpc')) {
            self::markTestSkipped('Must enable grpc extension.');
        }
        self::$instanceId = self::requireEnv('GOOGLE_SPANNER_INSTANCE_ID');

        $spanner = new SpannerClient([
            'projectId' => self::$projectId,
        ]);

        self::$databaseId = 'test-' . time() . rand();
        self::$backupId = 'backup-' . self::$databaseId;
        self::$instance = $spanner->instance(self::$instanceId);
    }

    public function testCreateDatabase()
    {
        $output = $this->runCommand('create-database');
        $this->assertContains('Waiting for operation to complete...', $output);
        $this->assertContains('Created database test-', $output);
    }

    /**
     * @depends testCreateDatabase
     */
    public function testInsertData()
    {
        $output = $this->runCommand('insert-data');
        $this->assertEquals('Inserted data.' . PHP_EOL, $output);
    }

    /**
     * @depends testInsertData
     */
    public function testQueryData()
    {
        $output = $this->runCommand('query-data');
        $this->assertContains('SingerId: 1, AlbumId: 1, AlbumTitle: Total Junk', $output);
        $this->assertContains('SingerId: 1, AlbumId: 2, AlbumTitle: Go, Go, Go', $output);
        $this->assertContains('SingerId: 2, AlbumId: 1, AlbumTitle: Green', $output);
        $this->assertContains('SingerId: 2, AlbumId: 2, AlbumTitle: Forever Hold Your Peace', $output);
        $this->assertContains('SingerId: 2, AlbumId: 3, AlbumTitle: Terrified', $output);
    }

    /**
     * @depends testInsertData
     */
    public function testBatchQueryData()
    {
        $output = $this->runCommand('batch-query-data');
        $this->assertContains('SingerId: 1, FirstName: Marc, LastName: Richards', $output);
        $this->assertContains('SingerId: 2, FirstName: Catalina, LastName: Smith', $output);
        $this->assertContains('SingerId: 3, FirstName: Alice, LastName: Trentor', $output);
        $this->assertContains('SingerId: 4, FirstName: Lea, LastName: Martin', $output);
        $this->assertContains('SingerId: 5, FirstName: David, LastName: Lomond', $output);
        $this->assertContains('Total Partitions:', $output);
        $this->assertContains('Total Records: 5', $output);
        $this->assertContains('Average Records Per Partition:', $output);
    }

    /**
     * @depends testInsertData
     */
    public function testReadData()
    {
        $output = $this->runCommand('read-data');
        $this->assertContains('SingerId: 1, AlbumId: 1, AlbumTitle: Total Junk', $output);
        $this->assertContains('SingerId: 1, AlbumId: 2, AlbumTitle: Go, Go, Go', $output);
        $this->assertContains('SingerId: 2, AlbumId: 1, AlbumTitle: Green', $output);
        $this->assertContains('SingerId: 2, AlbumId: 2, AlbumTitle: Forever Hold Your Peace', $output);
        $this->assertContains('SingerId: 2, AlbumId: 3, AlbumTitle: Terrified', $output);
    }

    /**
     * @depends testInsertData
     */
    public function testAddColumn()
    {
        $output = $this->runCommand('add-column');
        $this->assertContains('Waiting for operation to complete...', $output);
        $this->assertContains('Added the MarketingBudget column.', $output);
    }

    /**
     * @depends testAddColumn
     */
    public function testUpdateData()
    {
        $output = $this->runCommand('update-data');
        self::$lastUpdateDataTimestamp = time();
        $this->assertEquals('Updated data.' . PHP_EOL, $output);
    }

    /**
     * @depends testAddColumn
     */
    public function testQueryDataWithNewColumn()
    {
        $output = $this->runCommand('query-data-with-new-column');
        $this->assertContains('SingerId: 1, AlbumId: 1, MarketingBudget:', $output);
        $this->assertContains('SingerId: 1, AlbumId: 2, MarketingBudget:', $output);
        $this->assertContains('SingerId: 2, AlbumId: 1, MarketingBudget:', $output);
        $this->assertContains('SingerId: 2, AlbumId: 2, MarketingBudget:', $output);
        $this->assertContains('SingerId: 2, AlbumId: 3, MarketingBudget:', $output);
    }

    /**
     * @depends testUpdateData
     */
    public function testReadWriteTransaction()
    {
        $this->runCommand('update-data');
        $output = $this->runCommand('read-write-transaction');
        $this->assertContains('Setting first album\'s budget to 300000 and the second album\'s budget to 300000', $output);
        $this->assertContains('Transaction complete.', $output);
    }

    /**
     * @depends testAddColumn
     */
    public function testCreateIndex()
    {
        $output = $this->runCommand('create-index');
        $this->assertContains('Waiting for operation to complete...', $output);
        $this->assertContains('Added the AlbumsByAlbumTitle index.', $output);
    }

    /**
     * @depends testCreateIndex
     */
    public function testQueryDataWithIndex()
    {
        $output = $this->runCommand('query-data-with-index');
        $this->assertContains('AlbumId: 2, AlbumTitle: Forever Hold Your Peace', $output);
        $this->assertContains('AlbumId: 2, AlbumTitle: Go, Go, Go', $output);
    }

    /**
     * @depends testCreateIndex
     */
    public function testReadDataWithIndex()
    {
        $output = $this->runCommand('read-data-with-index');

        $this->assertContains('AlbumId: 1, AlbumTitle: Total Junk', $output);
        $this->assertContains('AlbumId: 2, AlbumTitle: Go, Go, Go', $output);
        $this->assertContains('AlbumId: 1, AlbumTitle: Green', $output);
        $this->assertContains('AlbumId: 3, AlbumTitle: Terrified', $output);
        $this->assertContains('AlbumId: 2, AlbumTitle: Forever Hold Your Peace', $output);
    }

    /**
     * @depends testCreateIndex
     */
    public function testCreateStoringIndex()
    {
        $output = $this->runCommand('create-storing-index');
        $this->assertContains('Waiting for operation to complete...', $output);
        $this->assertContains('Added the AlbumsByAlbumTitle2 index.', $output);
    }

    /**
     * @depends testCreateStoringIndex
     */
    public function testReadDataWithStoringIndex()
    {
        $output = $this->runCommand('read-data-with-storing-index');
        $this->assertContains('AlbumId: 2, AlbumTitle: Forever Hold Your Peace, MarketingBudget:', $output);
        $this->assertContains('AlbumId: 2, AlbumTitle: Go, Go, Go, MarketingBudget:', $output);
        $this->assertContains('AlbumId: 1, AlbumTitle: Green, MarketingBudget:', $output);
        $this->assertContains('AlbumId: 3, AlbumTitle: Terrified, MarketingBudget:', $output);
        $this->assertContains('AlbumId: 1, AlbumTitle: Total Junk, MarketingBudget:', $output);
    }

    /**
     * @depends testUpdateData
     */
    public function testReadOnlyTransaction()
    {
        $output = $this->runCommand('read-only-transaction');
        $this->assertContains('SingerId: 1, AlbumId: 1, AlbumTitle: Total Junk', $output);
        $this->assertContains('SingerId: 1, AlbumId: 2, AlbumTitle: Go, Go, Go', $output);
        $this->assertContains('SingerId: 2, AlbumId: 1, AlbumTitle: Green', $output);
        $this->assertContains('SingerId: 2, AlbumId: 2, AlbumTitle: Forever Hold Your Peace', $output);
        $this->assertContains('SingerId: 2, AlbumId: 3, AlbumTitle: Terrified', $output);
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
        $output = $this->runCommand('read-stale-data');
        $this->assertContains('SingerId: 1, AlbumId: 1, AlbumTitle: Total Junk', $output);
        $this->assertContains('SingerId: 1, AlbumId: 2, AlbumTitle: Go, Go, Go', $output);
        $this->assertContains('SingerId: 2, AlbumId: 1, AlbumTitle: Green', $output);
        $this->assertContains('SingerId: 2, AlbumId: 2, AlbumTitle: Forever Hold Your Peace', $output);
        $this->assertContains('SingerId: 2, AlbumId: 3, AlbumTitle: Terrified', $output);
    }

    /**
     * @depends testReadStaleData
     */
    public function testCreateTableTimestamp()
    {
        $output = $this->runCommand('create-table-timestamp');
        $this->assertContains('Waiting for operation to complete...', $output);
        $this->assertContains('Created Performances table in database test-', $output);
    }

    /**
     * @depends testCreateTableTimestamp
     */
    public function testInsertDataTimestamp()
    {
        $output = $this->runCommand('insert-data-timestamp');
        $this->assertEquals('Inserted data.' . PHP_EOL, $output);
    }

    /**
     * @depends testInsertDataTimestamp
     */
    public function testAddTimestampColumn()
    {
        $output = $this->runCommand('add-timestamp-column');
        $this->assertContains('Waiting for operation to complete...', $output);
        $this->assertContains('Added LastUpdateTime as a commit timestamp column in Albums table', $output);
    }

    /**
     * @depends testAddTimestampColumn
     */
    public function testUpdateDataTimestamp()
    {
        $output = $this->runCommand('update-data-timestamp');
        $this->assertEquals('Updated data.' . PHP_EOL, $output);
    }

    /**
     * @depends testUpdateDataTimestamp
     */
    public function testQueryDataTimestamp()
    {
        $output = $this->runCommand('query-data-timestamp');
        $this->assertContains('SingerId: 1, AlbumId: 1, MarketingBudget: 1000000, LastUpdateTime: 20', $output);
        $this->assertContains('SingerId: 2, AlbumId: 2, MarketingBudget: 750000, LastUpdateTime: 20', $output);
        $this->assertContains('SingerId: 1, AlbumId: 2, MarketingBudget: NULL, LastUpdateTime: NULL', $output);
        $this->assertContains('SingerId: 2, AlbumId: 1, MarketingBudget: NULL, LastUpdateTime: NULL', $output);
        $this->assertContains('SingerId: 2, AlbumId: 3, MarketingBudget: NULL, LastUpdateTime: NULL', $output);
    }

    /**
     * @depends testCreateDatabase
     */
    public function testInsertStructData()
    {
        $output = $this->runCommand('insert-struct-data');
        $this->assertEquals('Inserted data.' . PHP_EOL, $output);
    }

    /**
     * @depends testInsertStructData
     */
    public function testQueryDataWithStruct()
    {
        $output = $this->runCommand('query-data-with-struct');
        $this->assertContains('SingerId: 6', $output);
    }

    /**
     * @depends testInsertStructData
     */
    public function testQueryDataWithArrayOfStruct()
    {
        $output = $this->runCommand('query-data-with-array-of-struct');
        $this->assertContains('SingerId: 6', $output);
        $this->assertContains('SingerId: 7', $output);
        $this->assertContains('SingerId: 8', $output);
    }

    /**
     * @depends testInsertStructData
     */
    public function testQueryDataWithStructField()
    {
        $output = $this->runCommand('query-data-with-struct-field');
        $this->assertContains('SingerId: 6', $output);
    }

    /**
     * @depends testInsertStructData
     */
    public function testQueryDataWithNestedStructField()
    {
        $output = $this->runCommand('query-data-with-nested-struct-field');
        $this->assertContains('SingerId: 6 SongName: Imagination', $output);
        $this->assertContains('SingerId: 9 SongName: Imagination', $output);
    }

    /**
     * @depends testCreateDatabase
     */
    public function testInsertDataWithDml()
    {
        $output = $this->runCommand('insert-data-with-dml');
        $this->assertContains('Inserted 1 row(s)', $output);
    }

    /**
     * @depends testAddColumn
     */
    public function testUpdateDataWithDml()
    {
        $output = $this->runCommand('update-data-with-dml');
        self::$lastUpdateDataTimestamp = time();
        $this->assertContains('Updated 1 row(s)', $output);
    }

    /**
     * @depends testAddColumn
     */
    public function testDeleteDataWithDml()
    {
        $output = $this->runCommand('delete-data-with-dml');
        self::$lastUpdateDataTimestamp = time();
        $this->assertContains('Deleted 1 row(s)', $output);
    }

    /**
     * @depends testInsertData
     */
    public function testUpdateDataWithDmlTimestamp()
    {
        $output = $this->runCommand('update-data-with-dml-timestamp');
        self::$lastUpdateDataTimestamp = time();
        $this->assertContains('Updated 2 row(s)', $output);
    }

    /**
     * @depends testCreateDatabase
     */
    public function testWriteReadWithDml()
    {
        $output = $this->runCommand('write-read-with-dml');
        self::$lastUpdateDataTimestamp = time();
        $this->assertContains('Timothy Campbell', $output);
    }

    /**
     * @depends testCreateDatabase
     */
    public function testUpdateDataWithDmlStructs()
    {
        $output = $this->runCommand('update-data-with-dml-structs');
        self::$lastUpdateDataTimestamp = time();
        $this->assertContains('Updated 1 row(s)', $output);
    }

    /**
     * @depends testInsertData
     */
    public function testWriteDataWithDML()
    {
        $output = $this->runCommand('write-data-with-dml');
        self::$lastUpdateDataTimestamp = time();
        $this->assertContains('Inserted 4 row(s)', $output);
    }

    /**
     * @depends testWriteDataWithDML
     */
    public function testQueryDataWithParameter()
    {
        $output = $this->runCommand('query-data-with-parameter');
        self::$lastUpdateDataTimestamp = time();
        $this->assertContains('SingerId: 12, FirstName: Melissa, LastName: Garcia', $output);
    }

    /**
     * @depends testAddColumn
     */
    public function testUpdateDataWithDmlTransaction()
    {
        $output = $this->runCommand('write-data-with-dml-transaction');
        self::$lastUpdateDataTimestamp = time();
        $this->assertContains('Transaction complete', $output);
    }

    /**
     * @depends testAddColumn
     */
    public function testUpdateDataWithPartitionedDML()
    {
        $output = $this->runCommand('update-data-with-partitioned-dml');
        self::$lastUpdateDataTimestamp = time();
        $this->assertContains('Updated 3 row(s)', $output);
    }

    /**
     * @depends testAddColumn
     */
    public function testDeleteDataWithPartitionedDML()
    {
        $output = $this->runCommand('deleted-data-with-partitioned-dml');
        self::$lastUpdateDataTimestamp = time();
        $this->assertContains('Deleted 5 row(s)', $output);
    }

    /**
     * @depends testAddColumn
     */
    public function testUpdateDataWithBatchDML()
    {
        $output = $this->runCommand('update-data-with-batch-dml');
        self::$lastUpdateDataTimestamp = time();
        $this->assertContains('Executed 2 SQL statements using Batch DML', $output);
    }

    /**
     * @depends testCreateDatabase
     */
    public function testCreateTableDatatypes()
    {
        $output = $this->runCommand('create-table-with-datatypes');
        $this->assertContains('Waiting for operation to complete...', $output);
        $this->assertContains('Created Venues table in database test-', $output);
    }

    /**
     * @depends testCreateTableDatatypes
     */
    public function testInsertDataWithDatatypes()
    {
        $output = $this->runCommand('insert-data-with-datatypes');
        $this->assertEquals('Inserted data.' . PHP_EOL, $output);
    }

    /**
     * @depends testInsertDataWithDatatypes
     */
    public function testQueryDataWithArrayParameter()
    {
        $output = $this->runCommand('query-data-with-array-parameter');
        self::$lastUpdateDataTimestamp = time();
        $this->assertContains('VenueId: 19, VenueName: Venue 19, AvailableDate: 2020-11-01', $output);
        $this->assertContains('VenueId: 42, VenueName: Venue 42, AvailableDate: 2020-10-01', $output);
    }

    /**
     * @depends testInsertDataWithDatatypes
     */
    public function testQueryDataWithBoolParameter()
    {
        $output = $this->runCommand('query-data-with-bool-parameter');
        self::$lastUpdateDataTimestamp = time();
        $this->assertContains('VenueId: 19, VenueName: Venue 19, OutdoorVenue: True', $output);
    }

    /**
     * @depends testInsertDataWithDatatypes
     */
    public function testQueryDataWithBytesParameter()
    {
        $output = $this->runCommand('query-data-with-bytes-parameter');
        self::$lastUpdateDataTimestamp = time();
        $this->assertContains('VenueId: 4, VenueName: Venue 4', $output);
    }

    /**
     * @depends testInsertDataWithDatatypes
     */
    public function testQueryDataWithDateParameter()
    {
        $output = $this->runCommand('query-data-with-date-parameter');
        self::$lastUpdateDataTimestamp = time();
        $this->assertContains('VenueId: 4, VenueName: Venue 4, LastContactDate: 2018-09-02', $output);
        $this->assertContains('VenueId: 42, VenueName: Venue 42, LastContactDate: 2018-10-01', $output);
    }

    /**
     * @depends testInsertDataWithDatatypes
     */
    public function testQueryDataWithFloatParameter()
    {
        $output = $this->runCommand('query-data-with-float-parameter');
        self::$lastUpdateDataTimestamp = time();
        $this->assertContains('VenueId: 4, VenueName: Venue 4, PopularityScore: 0.8', $output);
        $this->assertContains('VenueId: 19, VenueName: Venue 19, PopularityScore: 0.9', $output);
    }

    /**
     * @depends testInsertDataWithDatatypes
     */
    public function testQueryDataWithIntParameter()
    {
        $output = $this->runCommand('query-data-with-int-parameter');
        self::$lastUpdateDataTimestamp = time();
        $this->assertContains('VenueId: 19, VenueName: Venue 19, Capacity: 6300', $output);
        $this->assertContains('VenueId: 42, VenueName: Venue 42, Capacity: 3000', $output);
    }

    /**
     * @depends testInsertDataWithDatatypes
     */
    public function testQueryDataWithStringParameter()
    {
        $output = $this->runCommand('query-data-with-string-parameter');
        self::$lastUpdateDataTimestamp = time();
        $this->assertContains('VenueId: 42, VenueName: Venue 42', $output);
    }

    /**
     * @depends testInsertDataWithDatatypes
     */
    public function testQueryDataWithTimestampParameter()
    {
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runCommand('query-data-with-timestamp-parameter');
            self::$lastUpdateDataTimestamp = time();
            $this->assertContains('VenueId: 4, VenueName: Venue 4, LastUpdateTime:', $output);
            $this->assertContains('VenueId: 19, VenueName: Venue 19, LastUpdateTime:', $output);
            $this->assertContains('VenueId: 42, VenueName: Venue 42, LastUpdateTime:', $output);
        });
    }

    /**
     * @depends testInsertDataWithDatatypes
     */
    public function testQueryDataWithQueryOptions()
    {
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runCommand('query-data-with-query-options');
            self::$lastUpdateDataTimestamp = time();
            $this->assertContains('VenueId: 4, VenueName: Venue 4, LastUpdateTime:', $output);
            $this->assertContains('VenueId: 19, VenueName: Venue 19, LastUpdateTime:', $output);
            $this->assertContains('VenueId: 42, VenueName: Venue 42, LastUpdateTime:', $output);
        });
    }

    /**
     * @depends testInsertDataWithDatatypes
     */
    public function testCreateClientWithQueryOptions()
    {
        $this->runEventuallyConsistentTest(function () {
            $output = $this->runCommand('create-client-with-query-options');
            self::$lastUpdateDataTimestamp = time();
            $this->assertContains('VenueId: 4, VenueName: Venue 4, LastUpdateTime:', $output);
            $this->assertContains('VenueId: 19, VenueName: Venue 19, LastUpdateTime:', $output);
            $this->assertContains('VenueId: 42, VenueName: Venue 42, LastUpdateTime:', $output);
        });
    }

    private function runCommand($commandName)
    {
        return $this->traitRunCommand($commandName, [
            'instance_id' => self::$instanceId,
            'database_id' => self::$databaseId,
        ]);
    }

    public static function tearDownAfterClass()
    {
        if (self::$instance->exists()) {// Clean up database
            $database = self::$instance->database(self::$databaseId);
            $database->drop();
        }
    }
}
