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

namespace Google\Cloud\Samples\Bigable\Tests;

use Google\Cloud\Bigtable\Admin\V2\BigtableInstanceAdminClient;
use Google\Cloud\Bigtable\Admin\V2\ColumnFamily;
use PHPUnit\Framework\TestCase;

use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;
use Google\Cloud\Bigtable\Admin\V2\Table;
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\ExponentialBackoffTrait;

final class WriteTest extends TestCase
{
    use TestTrait;
    use ExponentialBackoffTrait;

    const INSTANCE_ID_PREFIX = 'phpunit-test-';
    const TABLE_ID_PREFIX = 'mobile-time-series-';
    private static $bigtableInstanceAdminClient;
    private static $bigtableTableAdminClient;
    private static $instanceId;
    private static $tableId;

    public static function setUpBeforeClass()
    {
        self::checkProjectEnvVarBeforeClass();

        self::$bigtableInstanceAdminClient = new BigtableInstanceAdminClient();
        self::$bigtableTableAdminClient = new BigtableTableAdminClient();
        self::$instanceId = uniqid(self::INSTANCE_ID_PREFIX);
        self::runSnippet('create_dev_instance', [
            self::$projectId,
            self::$instanceId,
            self::$instanceId,
        ]);

        self::$tableId = uniqid(self::TABLE_ID_PREFIX);

        $formattedParent = self::$bigtableTableAdminClient
            ->instanceName(self::$projectId, self::$instanceId);
        $table = (new Table())->setColumnFamilies(["stats_summary" => new ColumnFamily()]);
        self::$bigtableTableAdminClient->createtable(
            $formattedParent,
            self::$tableId,
            $table
        );
    }

    public function setUp()
    {
        $this->useResourceExhaustedBackoff();
    }

    public static function tearDownAfterClass()
    {
        $instanceName = self::$bigtableInstanceAdminClient->instanceName(self::$projectId, self::$instanceId);
        self::$bigtableInstanceAdminClient->deleteInstance($instanceName);
    }

    public function testWriteSimple()
    {
        $output = $this->runSnippet('writes/write_simple', [
            self::$projectId,
            self::$instanceId,
            self::$tableId
        ]);

        $this->assertContains('Successfully wrote row.', $output);
    }

    public function testWriteConditional()
    {
        $output = $this->runSnippet('writes/write_conditionally', [
            self::$projectId,
            self::$instanceId,
            self::$tableId
        ]);

        $this->assertContains('Successfully updated row\'s os_name', $output);
    }

    public function testWriteIncrement()
    {
        $output = $this->runSnippet('writes/write_increment', [
            self::$projectId,
            self::$instanceId,
            self::$tableId
        ]);

        $this->assertContains('Successfully updated row.', $output);
    }

    public function testWriteBatch()
    {
        $this->requireGrpc();

        $output = $this->runSnippet('writes/write_batch', [
            self::$projectId,
            self::$instanceId,
            self::$tableId
        ]);

        $this->assertContains('Successfully wrote 2 rows.', $output);
    }
}
