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

use Google\Cloud\Bigtable\Mutations;
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 */
final class ReadTest extends TestCase
{
    use BigtableTestTrait;

    const INSTANCE_ID_PREFIX = 'phpunit-test-';
    const TABLE_ID_PREFIX = 'mobile-time-series-';

    private static $timestampMicros;

    public static function setUpBeforeClass(): void
    {
        self::requireGrpc();
        self::setUpBigtableVars();
        self::$instanceId = self::createDevInstance(self::INSTANCE_ID_PREFIX);
        self::$tableId = self::createTable(self::TABLE_ID_PREFIX);

        self::$timestampMicros = time() * 1000 * 1000;
        self::$bigtableClient->table(self::$instanceId, self::$tableId)->mutateRows([
            'phone#4c410523#20190501' => (new Mutations())
                ->upsert('stats_summary', 'connected_cell', 1, self::$timestampMicros)
                ->upsert('stats_summary', 'connected_wifi', 1, self::$timestampMicros)
                ->upsert('stats_summary', 'os_build', 'PQ2A.190405.003', self::$timestampMicros),
            'phone#4c410523#20190502' => (new Mutations())
                ->upsert('stats_summary', 'connected_cell', 1, self::$timestampMicros)
                ->upsert('stats_summary', 'connected_wifi', 1, self::$timestampMicros)
                ->upsert('stats_summary', 'os_build', 'PQ2A.190405.004', self::$timestampMicros),
            'phone#4c410523#20190505' => (new Mutations())
                ->upsert('stats_summary', 'connected_cell', 0, self::$timestampMicros)
                ->upsert('stats_summary', 'connected_wifi', 1, self::$timestampMicros)
                ->upsert('stats_summary', 'os_build', 'PQ2A.190406.000', self::$timestampMicros),
            'phone#5c10102#20190501' => (new Mutations())
                ->upsert('stats_summary', 'connected_cell', 1, self::$timestampMicros)
                ->upsert('stats_summary', 'connected_wifi', 1, self::$timestampMicros)
                ->upsert('stats_summary', 'os_build', 'PQ2A.190401.002', self::$timestampMicros),
            'phone#5c10102#20190502' => (new Mutations())
                ->upsert('stats_summary', 'connected_cell', 1, self::$timestampMicros)
                ->upsert('stats_summary', 'connected_wifi', 0, self::$timestampMicros)
                ->upsert('stats_summary', 'os_build', 'PQ2A.190406.000', self::$timestampMicros)
        ]);
    }

    public function setUp(): void
    {
        $this->useResourceExhaustedBackoff();
    }

    public static function tearDownAfterClass(): void
    {
        self::deleteBigtableInstance();
    }

    public function testReadRow()
    {
        $output = self::runFunctionSnippet('read_row', [
            self::$projectId,
            self::$instanceId,
            self::$tableId
        ]);

        $result = sprintf('Reading data for row phone#4c410523#20190501
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.003 @%1$s', self::$timestampMicros);

        $this->assertEquals($result, trim($output));
    }

    public function testReadRowPartial()
    {
        $output = self::runFunctionSnippet('read_row_partial', [
            self::$projectId,
            self::$instanceId,
            self::$tableId
        ]);

        $result = sprintf('Reading data for row phone#4c410523#20190501
Column Family stats_summary
	os_build: PQ2A.190405.003 @%1$s', self::$timestampMicros);

        $this->assertEquals($result, trim($output));
    }

    public function testReadRows()
    {
        $output = self::runFunctionSnippet('read_rows', [
            self::$projectId,
            self::$instanceId,
            self::$tableId
        ]);

        $result = sprintf('Reading data for row phone#4c410523#20190501
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.003 @%1$s

Reading data for row phone#4c410523#20190502
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.004 @%1$s', self::$timestampMicros);

        $this->assertEquals($result, trim($output));
    }

    public function testReadRowRange()
    {
        $output = self::runFunctionSnippet('read_row_range', [
            self::$projectId,
            self::$instanceId,
            self::$tableId
        ]);

        $result = sprintf('Reading data for row phone#4c410523#20190501
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.003 @%1$s

Reading data for row phone#4c410523#20190502
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.004 @%1$s

Reading data for row phone#4c410523#20190505
Column Family stats_summary
	connected_cell: 0 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190406.000 @%1$s', self::$timestampMicros);

        $this->assertEquals($result, trim($output));
    }

    public function testReadRowRanges()
    {
        $output = self::runFunctionSnippet('read_row_ranges', [
            self::$projectId,
            self::$instanceId,
            self::$tableId
        ]);

        $result = sprintf('Reading data for row phone#4c410523#20190501
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.003 @%1$s

Reading data for row phone#4c410523#20190502
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.004 @%1$s

Reading data for row phone#4c410523#20190505
Column Family stats_summary
	connected_cell: 0 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190406.000 @%1$s

Reading data for row phone#5c10102#20190501
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190401.002 @%1$s

Reading data for row phone#5c10102#20190502
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 0 @%1$s
	os_build: PQ2A.190406.000 @%1$s', self::$timestampMicros);

        $this->assertEquals($result, trim($output));
    }

    public function testReadPrefix()
    {
        $output = self::runFunctionSnippet('read_prefix', [
            self::$projectId,
            self::$instanceId,
            self::$tableId
        ]);

        $result = sprintf('Reading data for row phone#4c410523#20190501
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.003 @%1$s

Reading data for row phone#4c410523#20190502
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.004 @%1$s

Reading data for row phone#4c410523#20190505
Column Family stats_summary
	connected_cell: 0 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190406.000 @%1$s

Reading data for row phone#5c10102#20190501
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190401.002 @%1$s

Reading data for row phone#5c10102#20190502
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 0 @%1$s
	os_build: PQ2A.190406.000 @%1$s', self::$timestampMicros);

        $this->assertEquals($result, trim($output));
    }

    public function testReadFilter()
    {
        $output = self::runFunctionSnippet('read_filter', [
            self::$projectId,
            self::$instanceId,
            self::$tableId
        ]);

        $result = sprintf('Reading data for row phone#4c410523#20190501
Column Family stats_summary
	os_build: PQ2A.190405.003 @%1$s

Reading data for row phone#4c410523#20190502
Column Family stats_summary
	os_build: PQ2A.190405.004 @%1$s

Reading data for row phone#4c410523#20190505
Column Family stats_summary
	os_build: PQ2A.190406.000 @%1$s

Reading data for row phone#5c10102#20190501
Column Family stats_summary
	os_build: PQ2A.190401.002 @%1$s

Reading data for row phone#5c10102#20190502
Column Family stats_summary
	os_build: PQ2A.190406.000 @%1$s', self::$timestampMicros);

        $this->assertEquals($result, trim($output));
    }
}
