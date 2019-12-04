<?php

/**
 * Copyright 2019 Google LLC.
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

namespace Google\Cloud\Samples\Bigtable\Tests;

use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\ColumnFamily;
use Google\Cloud\Bigtable\BigtableClient;
use Google\Cloud\Bigtable\DataUtil;
use Google\Cloud\Bigtable\Mutations;
use PHPUnit\Framework\TestCase;

use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;
use Google\Cloud\Bigtable\Admin\V2\Table;
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\ExponentialBackoffTrait;

final class ReadTest extends TestCase
{
    use TestTrait;
    use ExponentialBackoffTrait;

    const INSTANCE_ID_PREFIX = 'phpunit-test-';
    const TABLE_ID_PREFIX = 'mobile-time-series-';
    private static $bigtableInstanceAdminClient;
    private static $bigtableTableAdminClient;
    private static $instanceId;
    private static $tableId;
    private static $timestamp;

    public static function setUpBeforeClass(): void
    {
        self::checkProjectEnvVarBeforeClass();

        self::$bigtableInstanceAdminClient = new BigtableInstanceAdminClient();
        self::$bigtableTableAdminClient = new BigtableTableAdminClient();
        self::$instanceId = "php-instance-5de5a80085438";
//        self::$instanceId = uniqid(self::INSTANCE_ID_PREFIX);
//        self::runSnippet('create_dev_instance', [
//            self::$projectId,
//            self::$instanceId,
//            self::$instanceId,
//        ]);

        self::$tableId = uniqid(self::TABLE_ID_PREFIX);

        $formattedParent = self::$bigtableTableAdminClient
            ->instanceName(self::$projectId, self::$instanceId);
        $table = (new Table())->setColumnFamilies(["stats_summary" => new ColumnFamily()]);
        self::$bigtableTableAdminClient->createtable(
            $formattedParent,
            self::$tableId,
            $table
        );

        $dataClient = new BigtableClient([
            'projectId' => self::$projectId,
        ]);

        $table = $dataClient->table(self::$instanceId, self::$tableId);

        self::$timestamp = time() * 1000;
        $table->mutateRows([
            "phone#4c410523#20190501" => (new Mutations())
                ->upsert('stats_summary', "connected_cell", 1, self::$timestamp)
                ->upsert('stats_summary', "connected_wifi", 1, self::$timestamp)
                ->upsert('stats_summary', "os_build", "PQ2A.190405.003", self::$timestamp),
            "phone#4c410523#20190502" => (new Mutations())
                ->upsert('stats_summary', "connected_cell", 1, self::$timestamp)
                ->upsert('stats_summary', "connected_wifi", 1, self::$timestamp)
                ->upsert('stats_summary', "os_build", "PQ2A.190405.004", self::$timestamp),
            "phone#4c410523#20190505" => (new Mutations())
                ->upsert('stats_summary', "connected_cell", 0, self::$timestamp)
                ->upsert('stats_summary', "connected_wifi", 1, self::$timestamp)
                ->upsert('stats_summary', "os_build", "PQ2A.190406.000", self::$timestamp),
            "phone#5c10102#20190501" => (new Mutations())
                ->upsert('stats_summary', "connected_cell", 1, self::$timestamp)
                ->upsert('stats_summary', "connected_wifi", 1, self::$timestamp)
                ->upsert('stats_summary', "os_build", "PQ2A.190401.002", self::$timestamp),
            "phone#5c10102#20190502" => (new Mutations())
                ->upsert('stats_summary', "connected_cell", 1, self::$timestamp)
                ->upsert('stats_summary', "connected_wifi", 0, self::$timestamp)
                ->upsert('stats_summary', "os_build", "PQ2A.190406.000", self::$timestamp)
        ]);
    }

    public function setUp(): void
    {
        $this->useResourceExhaustedBackoff();
    }

    public static function tearDownAfterClass(): void
    {
        $instanceName = self::$bigtableInstanceAdminClient->instanceName(self::$projectId, self::$instanceId);
//        self::$bigtableInstanceAdminClient->deleteInstance($instanceName);
        $tableName = self::$bigtableTableAdminClient->tableName(self::$projectId, self::$instanceId, self::$tableId);
        self::$bigtableTableAdminClient->deleteTable($tableName);

    }

    /**
     * @runInSeparateProcess
     */
    public function testReadRow()
    {
        $output = self::runSnippet('read_snippets', [
            self::$projectId,
            self::$instanceId,
            self::$tableId,
            "read_row"
        ]);

        $result = sprintf('Reading data for row
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.003 @%1$s', self::$timestamp);

        $this->assertEquals($result, trim($output));
    }

    /**
     * @runInSeparateProcess
     */
    public function testReadRowPartial()
    {
        $output = self::runSnippet('read_snippets', [
            self::$projectId,
            self::$instanceId,
            self::$tableId,
            "read_row_partial"
        ]);

        $result = sprintf('Reading data for row
Column Family stats_summary
	os_build: PQ2A.190405.003 @%1$s', self::$timestamp);

        $this->assertEquals($result, trim($output));
    }

    /**
     * @runInSeparateProcess
     */
    public function testReadRows()
    {
        $output = self::runSnippet('read_snippets', [
            self::$projectId,
            self::$instanceId,
            self::$tableId,
            "read_rows"
        ]);

        $result = sprintf('Reading data for row
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.003 @%1$s

Reading data for row
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.004 @%1$s', self::$timestamp);

        $this->assertEquals($result, trim($output));
    }

    /**
     * @runInSeparateProcess
     */
    public function testReadRowRange()
    {
        $output = self::runSnippet('read_snippets', [
            self::$projectId,
            self::$instanceId,
            self::$tableId,
            "read_row_range"
        ]);

        $result = sprintf('Reading data for row
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.003 @%1$s

Reading data for row
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.004 @%1$s

Reading data for row
Column Family stats_summary
	connected_cell: 0 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190406.000 @%1$s', self::$timestamp);

        $this->assertEquals($result, trim($output));
    }

    /**
     * @runInSeparateProcess
     */
    public function testReadRowRanges()
    {
        $output = self::runSnippet('read_snippets', [
            self::$projectId,
            self::$instanceId,
            self::$tableId,
            "read_row_ranges"
        ]);

        $result = sprintf('Reading data for row
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.003 @%1$s

Reading data for row
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.004 @%1$s

Reading data for row
Column Family stats_summary
	connected_cell: 0 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190406.000 @%1$s

Reading data for row
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190401.002 @%1$s

Reading data for row
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 0 @%1$s
	os_build: PQ2A.190406.000 @%1$s', self::$timestamp);

        $this->assertEquals($result, trim($output));
    }

    /**
     * @runInSeparateProcess
     */
    public function testReadPrefix()
    {
        $output = self::runSnippet('read_snippets', [
            self::$projectId,
            self::$instanceId,
            self::$tableId,
            "read_prefix"
        ]);

        $result = sprintf('Reading data for row
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.003 @%1$s

Reading data for row
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.004 @%1$s

Reading data for row
Column Family stats_summary
	connected_cell: 0 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190406.000 @%1$s

Reading data for row
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190401.002 @%1$s

Reading data for row
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 0 @%1$s
	os_build: PQ2A.190406.000 @%1$s', self::$timestamp);

        $this->assertEquals($result, trim($output));
    }

    /**
     * @runInSeparateProcess
     */
    public function testReadFilter()
    {
        $output = self::runSnippet('read_snippets', [
            self::$projectId,
            self::$instanceId,
            self::$tableId,
            "read_filter"
        ]);

        $result = sprintf('Reading data for row
Column Family stats_summary
	os_build: PQ2A.190405.003 @%1$s

Reading data for row
Column Family stats_summary
	os_build: PQ2A.190405.004 @%1$s

Reading data for row
Column Family stats_summary
	os_build: PQ2A.190406.000 @%1$s

Reading data for row
Column Family stats_summary
	os_build: PQ2A.190401.002 @%1$s

Reading data for row
Column Family stats_summary
	os_build: PQ2A.190406.000 @%1$s', self::$timestamp);

        $this->assertEquals($result, trim($output));
    }
}
