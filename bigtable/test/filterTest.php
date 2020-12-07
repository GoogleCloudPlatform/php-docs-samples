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
use PHPUnitRetry\RetryTrait;

final class FilterTest extends TestCase
{
    use BigtableTestTrait;
    use RetryTrait;

    const INSTANCE_ID_PREFIX = 'phpunit-test-';
    const TABLE_ID_PREFIX = 'mobile-time-series-';

    private static $timestampMicros;
    private static $timestampMicrosMinusHr;

    public static function setUpBeforeClass(): void
    {
        self::requireGrpc();
        self::setUpBigtableVars();
        self::$instanceId = self::createDevInstance(self::INSTANCE_ID_PREFIX);
        self::$tableId = self::createTable(self::TABLE_ID_PREFIX, [
            'cell_plan',
            'stats_summary',
        ]);

        self::$timestampMicros = time() * 1000 * 1000;
        self::$timestampMicrosMinusHr = (time() - 60 * 60) * 1000 * 1000;
        self::$bigtableClient->table(self::$instanceId, self::$tableId)->mutateRows([
            "phone#4c410523#20190501" => (new Mutations())
                ->upsert('cell_plan', "data_plan_01gb", true, self::$timestampMicrosMinusHr)
                ->upsert('cell_plan', "data_plan_01gb", false, self::$timestampMicros)
                ->upsert('cell_plan', "data_plan_05gb", true, self::$timestampMicros)
                ->upsert('stats_summary', "connected_cell", 1, self::$timestampMicros)
                ->upsert('stats_summary', "connected_wifi", 1, self::$timestampMicros)
                ->upsert('stats_summary', "os_build", "PQ2A.190405.003", self::$timestampMicros),
            "phone#4c410523#20190502" => (new Mutations())
                ->upsert('cell_plan', "data_plan_05gb", true, self::$timestampMicros)
                ->upsert('stats_summary', "connected_cell", 1, self::$timestampMicros)
                ->upsert('stats_summary', "connected_wifi", 1, self::$timestampMicros)
                ->upsert('stats_summary', "os_build", "PQ2A.190405.004", self::$timestampMicros),
            "phone#4c410523#20190505" => (new Mutations())
                ->upsert('cell_plan', "data_plan_05gb", true, self::$timestampMicros)
                ->upsert('stats_summary', "connected_cell", 0, self::$timestampMicros)
                ->upsert('stats_summary', "connected_wifi", 1, self::$timestampMicros)
                ->upsert('stats_summary', "os_build", "PQ2A.190406.000", self::$timestampMicros),
            "phone#5c10102#20190501" => (new Mutations())
                ->upsert('cell_plan', "data_plan_10gb", true, self::$timestampMicros)
                ->upsert('stats_summary', "connected_cell", 1, self::$timestampMicros)
                ->upsert('stats_summary', "connected_wifi", 1, self::$timestampMicros)
                ->upsert('stats_summary', "os_build", "PQ2A.190401.002", self::$timestampMicros),
            "phone#5c10102#20190502" => (new Mutations())
                ->upsert('cell_plan', "data_plan_10gb", true, self::$timestampMicros)
                ->upsert('stats_summary', "connected_cell", 1, self::$timestampMicros)
                ->upsert('stats_summary', "connected_wifi", 0, self::$timestampMicros)
                ->upsert('stats_summary', "os_build", "PQ2A.190406.000", self::$timestampMicros)
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

    /**
     * @retryAttempts 3
     * @retryDelaySeconds 10
     */
    public function testFilterLimitRowSample()
    {
        $output = self::runSnippet('filter_snippets', [
            self::$projectId,
            self::$instanceId,
            self::$tableId,
            "filter_limit_row_sample"
        ]);
        $result = "Reading data for row ";
        $this->assertContains($result, trim($output));
    }

    public function testFilterLimitRowRegex()
    {
        $output = self::runSnippet('filter_snippets', [
            self::$projectId,
            self::$instanceId,
            self::$tableId,
            "filter_limit_row_regex"
        ]);

        $result = sprintf('Reading data for row phone#4c410523#20190501
Column Family cell_plan
	data_plan_01gb:  @%1$s
	data_plan_01gb: 1 @%2$s
	data_plan_05gb: 1 @%1$s
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.003 @%1$s

Reading data for row phone#5c10102#20190501
Column Family cell_plan
	data_plan_10gb: 1 @%1$s
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190401.002 @%1$s', self::$timestampMicros, self::$timestampMicrosMinusHr);

        $this->assertEquals($result, trim($output));
    }

    public function testFilterLimitCellsPerCol()
    {
        $output = self::runSnippet('filter_snippets', [
            self::$projectId,
            self::$instanceId,
            self::$tableId,
            "filter_limit_cells_per_col"
        ]);

        $result = sprintf('Reading data for row phone#4c410523#20190501
Column Family cell_plan
	data_plan_01gb:  @%1$s
	data_plan_01gb: 1 @%2$s
	data_plan_05gb: 1 @%1$s
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.003 @%1$s

Reading data for row phone#4c410523#20190502
Column Family cell_plan
	data_plan_05gb: 1 @%1$s
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.004 @%1$s

Reading data for row phone#4c410523#20190505
Column Family cell_plan
	data_plan_05gb: 1 @%1$s
Column Family stats_summary
	connected_cell: 0 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190406.000 @%1$s

Reading data for row phone#5c10102#20190501
Column Family cell_plan
	data_plan_10gb: 1 @%1$s
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190401.002 @%1$s

Reading data for row phone#5c10102#20190502
Column Family cell_plan
	data_plan_10gb: 1 @%1$s
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 0 @%1$s
	os_build: PQ2A.190406.000 @%1$s', self::$timestampMicros, self::$timestampMicrosMinusHr);

        $this->assertEquals($result, trim($output));
    }

    public function testFilterLimitCellsPerRow()
    {
        $output = self::runSnippet('filter_snippets', [
            self::$projectId,
            self::$instanceId,
            self::$tableId,
            "filter_limit_cells_per_row"
        ]);

        $result = sprintf('Reading data for row phone#4c410523#20190501
Column Family cell_plan
	data_plan_01gb:  @%1$s
	data_plan_01gb: 1 @%2$s

Reading data for row phone#4c410523#20190502
Column Family cell_plan
	data_plan_05gb: 1 @%1$s
Column Family stats_summary
	connected_cell: 1 @%1$s

Reading data for row phone#4c410523#20190505
Column Family cell_plan
	data_plan_05gb: 1 @%1$s
Column Family stats_summary
	connected_cell: 0 @%1$s

Reading data for row phone#5c10102#20190501
Column Family cell_plan
	data_plan_10gb: 1 @%1$s
Column Family stats_summary
	connected_cell: 1 @%1$s

Reading data for row phone#5c10102#20190502
Column Family cell_plan
	data_plan_10gb: 1 @%1$s
Column Family stats_summary
	connected_cell: 1 @%1$s', self::$timestampMicros, self::$timestampMicrosMinusHr);

        $this->assertEquals($result, trim($output));
    }

    public function testFilterLimitCellsPerRowOffset()
    {
        $output = self::runSnippet('filter_snippets', [
            self::$projectId,
            self::$instanceId,
            self::$tableId,
            "filter_limit_cells_per_row_offset"
        ]);

        $result = sprintf('Reading data for row phone#4c410523#20190501
Column Family cell_plan
	data_plan_05gb: 1 @%1$s
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.003 @%1$s

Reading data for row phone#4c410523#20190502
Column Family stats_summary
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.004 @%1$s

Reading data for row phone#4c410523#20190505
Column Family stats_summary
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190406.000 @%1$s

Reading data for row phone#5c10102#20190501
Column Family stats_summary
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190401.002 @%1$s

Reading data for row phone#5c10102#20190502
Column Family stats_summary
	connected_wifi: 0 @%1$s
	os_build: PQ2A.190406.000 @%1$s', self::$timestampMicros, self::$timestampMicrosMinusHr);

        $this->assertEquals($result, trim($output));
    }

    public function testFilterLimitColFamilyRegex()
    {
        $output = self::runSnippet('filter_snippets', [
            self::$projectId,
            self::$instanceId,
            self::$tableId,
            "filter_limit_col_family_regex"
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
	os_build: PQ2A.190406.000 @%1$s', self::$timestampMicros, self::$timestampMicrosMinusHr);

        $this->assertEquals($result, trim($output));
    }

    public function testFilterLimitColQualifierRegex()
    {
        $output = self::runSnippet('filter_snippets', [
            self::$projectId,
            self::$instanceId,
            self::$tableId,
            "filter_limit_col_qualifier_regex"
        ]);

        $result = sprintf('Reading data for row phone#4c410523#20190501
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s

Reading data for row phone#4c410523#20190502
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s

Reading data for row phone#4c410523#20190505
Column Family stats_summary
	connected_cell: 0 @%1$s
	connected_wifi: 1 @%1$s

Reading data for row phone#5c10102#20190501
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s

Reading data for row phone#5c10102#20190502
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 0 @%1$s', self::$timestampMicros, self::$timestampMicrosMinusHr);

        $this->assertEquals($result, trim($output));
    }

    public function testFilterLimitColRange()
    {
        $output = self::runSnippet('filter_snippets', [
            self::$projectId,
            self::$instanceId,
            self::$tableId,
            "filter_limit_col_range"
        ]);

        $result = sprintf('Reading data for row phone#4c410523#20190501
Column Family cell_plan
	data_plan_01gb:  @%1$s
	data_plan_01gb: 1 @%2$s
	data_plan_05gb: 1 @%1$s

Reading data for row phone#4c410523#20190502
Column Family cell_plan
	data_plan_05gb: 1 @%1$s

Reading data for row phone#4c410523#20190505
Column Family cell_plan
	data_plan_05gb: 1 @%1$s', self::$timestampMicros, self::$timestampMicrosMinusHr);

        $this->assertEquals($result, trim($output));
    }

    public function testFilterLimitValueRange()
    {
        $output = self::runSnippet('filter_snippets', [
            self::$projectId,
            self::$instanceId,
            self::$tableId,
            "filter_limit_value_range"
        ]);

        $result = sprintf('Reading data for row phone#4c410523#20190501
Column Family stats_summary
	os_build: PQ2A.190405.003 @%1$s

Reading data for row phone#4c410523#20190502
Column Family stats_summary
	os_build: PQ2A.190405.004 @%1$s', self::$timestampMicros, self::$timestampMicrosMinusHr);

        $this->assertEquals($result, trim($output));
    }

    public function testFilterLimitValueRegex()
    {
        $output = self::runSnippet('filter_snippets', [
            self::$projectId,
            self::$instanceId,
            self::$tableId,
            "filter_limit_value_regex"
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
	os_build: PQ2A.190406.000 @%1$s', self::$timestampMicros, self::$timestampMicrosMinusHr);

        $this->assertEquals($result, trim($output));
    }

    /**
     * @retryAttempts 3
     * @retryDelaySeconds 10
     */
    public function testFilterLimitTimestampRange()
    {
        $output = self::runSnippet('filter_snippets', [
            self::$projectId,
            self::$instanceId,
            self::$tableId,
            "filter_limit_timestamp_range"
        ]);

        $result = sprintf('Reading data for row phone#4c410523#20190501
Column Family cell_plan
	data_plan_01gb: 1 @%1$s', self::$timestampMicrosMinusHr);

        $this->assertEquals($result, trim($output));
    }

    public function testFilterLimitBlockAll()
    {
        $output = self::runSnippet('filter_snippets', [
            self::$projectId,
            self::$instanceId,
            self::$tableId,
            "filter_limit_block_all"
        ]);

        $result = "";

        $this->assertEquals($result, trim($output));
    }

    public function testFilterLimitPassAll()
    {
        $output = self::runSnippet('filter_snippets', [
            self::$projectId,
            self::$instanceId,
            self::$tableId,
            "filter_limit_pass_all"
        ]);

        $result = sprintf('Reading data for row phone#4c410523#20190501
Column Family cell_plan
	data_plan_01gb:  @%1$s
	data_plan_01gb: 1 @%2$s
	data_plan_05gb: 1 @%1$s
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.003 @%1$s

Reading data for row phone#4c410523#20190502
Column Family cell_plan
	data_plan_05gb: 1 @%1$s
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.004 @%1$s

Reading data for row phone#4c410523#20190505
Column Family cell_plan
	data_plan_05gb: 1 @%1$s
Column Family stats_summary
	connected_cell: 0 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190406.000 @%1$s

Reading data for row phone#5c10102#20190501
Column Family cell_plan
	data_plan_10gb: 1 @%1$s
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190401.002 @%1$s

Reading data for row phone#5c10102#20190502
Column Family cell_plan
	data_plan_10gb: 1 @%1$s
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 0 @%1$s
	os_build: PQ2A.190406.000 @%1$s', self::$timestampMicros, self::$timestampMicrosMinusHr);

        $this->assertEquals($result, trim($output));
    }

    public function testFilterModifyStripValue()
    {
        $output = self::runSnippet('filter_snippets', [
            self::$projectId,
            self::$instanceId,
            self::$tableId,
            "filter_modify_strip_value"
        ]);

        $result = sprintf('Reading data for row phone#4c410523#20190501
Column Family cell_plan
	data_plan_01gb:  @%1$s
	data_plan_01gb:  @%2$s
	data_plan_05gb:  @%1$s
Column Family stats_summary
	connected_cell:  @%1$s
	connected_wifi:  @%1$s
	os_build:  @%1$s

Reading data for row phone#4c410523#20190502
Column Family cell_plan
	data_plan_05gb:  @%1$s
Column Family stats_summary
	connected_cell:  @%1$s
	connected_wifi:  @%1$s
	os_build:  @%1$s

Reading data for row phone#4c410523#20190505
Column Family cell_plan
	data_plan_05gb:  @%1$s
Column Family stats_summary
	connected_cell:  @%1$s
	connected_wifi:  @%1$s
	os_build:  @%1$s

Reading data for row phone#5c10102#20190501
Column Family cell_plan
	data_plan_10gb:  @%1$s
Column Family stats_summary
	connected_cell:  @%1$s
	connected_wifi:  @%1$s
	os_build:  @%1$s

Reading data for row phone#5c10102#20190502
Column Family cell_plan
	data_plan_10gb:  @%1$s
Column Family stats_summary
	connected_cell:  @%1$s
	connected_wifi:  @%1$s
	os_build:  @%1$s', self::$timestampMicros, self::$timestampMicrosMinusHr);

        $this->assertEquals($result, trim($output));
    }

    public function testFilterModifyApplyLabel()
    {
        $output = self::runSnippet('filter_snippets', [
            self::$projectId,
            self::$instanceId,
            self::$tableId,
            "filter_modify_apply_label"
        ]);

        $result = sprintf('Reading data for row phone#4c410523#20190501
Column Family cell_plan
	data_plan_01gb:  @%1$s [labelled]
	data_plan_01gb: 1 @%2$s [labelled]
	data_plan_05gb: 1 @%1$s [labelled]
Column Family stats_summary
	connected_cell: 1 @%1$s [labelled]
	connected_wifi: 1 @%1$s [labelled]
	os_build: PQ2A.190405.003 @%1$s [labelled]

Reading data for row phone#4c410523#20190502
Column Family cell_plan
	data_plan_05gb: 1 @%1$s [labelled]
Column Family stats_summary
	connected_cell: 1 @%1$s [labelled]
	connected_wifi: 1 @%1$s [labelled]
	os_build: PQ2A.190405.004 @%1$s [labelled]

Reading data for row phone#4c410523#20190505
Column Family cell_plan
	data_plan_05gb: 1 @%1$s [labelled]
Column Family stats_summary
	connected_cell: 0 @%1$s [labelled]
	connected_wifi: 1 @%1$s [labelled]
	os_build: PQ2A.190406.000 @%1$s [labelled]

Reading data for row phone#5c10102#20190501
Column Family cell_plan
	data_plan_10gb: 1 @%1$s [labelled]
Column Family stats_summary
	connected_cell: 1 @%1$s [labelled]
	connected_wifi: 1 @%1$s [labelled]
	os_build: PQ2A.190401.002 @%1$s [labelled]

Reading data for row phone#5c10102#20190502
Column Family cell_plan
	data_plan_10gb: 1 @%1$s [labelled]
Column Family stats_summary
	connected_cell: 1 @%1$s [labelled]
	connected_wifi: 0 @%1$s [labelled]
	os_build: PQ2A.190406.000 @%1$s [labelled]', self::$timestampMicros, self::$timestampMicrosMinusHr);

        $this->assertEquals($result, trim($output));
    }

    public function testFilterComposingChain()
    {
        $output = self::runSnippet('filter_snippets', [
            self::$projectId,
            self::$instanceId,
            self::$tableId,
            "filter_composing_chain"
        ]);

        $result = sprintf('Reading data for row phone#4c410523#20190501
Column Family cell_plan
	data_plan_01gb:  @%1$s
	data_plan_05gb: 1 @%1$s

Reading data for row phone#4c410523#20190502
Column Family cell_plan
	data_plan_05gb: 1 @%1$s

Reading data for row phone#4c410523#20190505
Column Family cell_plan
	data_plan_05gb: 1 @%1$s

Reading data for row phone#5c10102#20190501
Column Family cell_plan
	data_plan_10gb: 1 @%1$s

Reading data for row phone#5c10102#20190502
Column Family cell_plan
	data_plan_10gb: 1 @%1$s', self::$timestampMicros);

        $this->assertEquals($result, trim($output));
    }

    public function testFilterComposingInterleave()
    {
        $output = self::runSnippet('filter_snippets', [
            self::$projectId,
            self::$instanceId,
            self::$tableId,
            "filter_composing_interleave"
        ]);

        $result = sprintf('Reading data for row phone#4c410523#20190501
Column Family cell_plan
	data_plan_01gb: 1 @%2$s
	data_plan_05gb: 1 @%1$s
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.003 @%1$s

Reading data for row phone#4c410523#20190502
Column Family cell_plan
	data_plan_05gb: 1 @%1$s
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190405.004 @%1$s

Reading data for row phone#4c410523#20190505
Column Family cell_plan
	data_plan_05gb: 1 @%1$s
Column Family stats_summary
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190406.000 @%1$s

Reading data for row phone#5c10102#20190501
Column Family cell_plan
	data_plan_10gb: 1 @%1$s
Column Family stats_summary
	connected_cell: 1 @%1$s
	connected_wifi: 1 @%1$s
	os_build: PQ2A.190401.002 @%1$s

Reading data for row phone#5c10102#20190502
Column Family cell_plan
	data_plan_10gb: 1 @%1$s
Column Family stats_summary
	connected_cell: 1 @%1$s
	os_build: PQ2A.190406.000 @%1$s', self::$timestampMicros, self::$timestampMicrosMinusHr);

        $this->assertEquals($result, trim($output));
    }

    public function testFilterComposingCondition()
    {
        $output = self::runSnippet('filter_snippets', [
            self::$projectId,
            self::$instanceId,
            self::$tableId,
            "filter_composing_condition"
        ]);

        $result = sprintf('Reading data for row phone#4c410523#20190501
Column Family cell_plan
	data_plan_01gb:  @%1$s [filtered-out]
	data_plan_01gb: 1 @%2$s [filtered-out]
	data_plan_05gb: 1 @%1$s [filtered-out]
Column Family stats_summary
	connected_cell: 1 @%1$s [filtered-out]
	connected_wifi: 1 @%1$s [filtered-out]
	os_build: PQ2A.190405.003 @%1$s [filtered-out]

Reading data for row phone#4c410523#20190502
Column Family cell_plan
	data_plan_05gb: 1 @%1$s [filtered-out]
Column Family stats_summary
	connected_cell: 1 @%1$s [filtered-out]
	connected_wifi: 1 @%1$s [filtered-out]
	os_build: PQ2A.190405.004 @%1$s [filtered-out]

Reading data for row phone#4c410523#20190505
Column Family cell_plan
	data_plan_05gb: 1 @%1$s [filtered-out]
Column Family stats_summary
	connected_cell: 0 @%1$s [filtered-out]
	connected_wifi: 1 @%1$s [filtered-out]
	os_build: PQ2A.190406.000 @%1$s [filtered-out]

Reading data for row phone#5c10102#20190501
Column Family cell_plan
	data_plan_10gb: 1 @%1$s [passed-filter]
Column Family stats_summary
	connected_cell: 1 @%1$s [passed-filter]
	connected_wifi: 1 @%1$s [passed-filter]
	os_build: PQ2A.190401.002 @%1$s [passed-filter]

Reading data for row phone#5c10102#20190502
Column Family cell_plan
	data_plan_10gb: 1 @%1$s [passed-filter]
Column Family stats_summary
	connected_cell: 1 @%1$s [passed-filter]
	connected_wifi: 0 @%1$s [passed-filter]
	os_build: PQ2A.190406.000 @%1$s [passed-filter]', self::$timestampMicros, self::$timestampMicrosMinusHr);

        $this->assertEquals($result, trim($output));
    }
}
