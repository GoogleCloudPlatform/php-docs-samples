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

use PHPUnit\Framework\TestCase;

use Google\Cloud\Bigtable\Admin\V2\BigtableTableAdminClient;
use Google\Cloud\Bigtable\Admin\V2\Table;
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\ExponentialBackoffTrait;

final class WriteTest extends TestCase
{
    use TestTrait;
    use ExponentialBackoffTrait;

    const TABLE_ID_PREFIX = 'mobile-time-series-';
    private static $tableAdminClient;
    private static $instanceId;
    private static $tableId;

    public static function setUpBeforeClass()
    {
        self::checkProjectEnvVarBeforeClass();

        self::$tableAdminClient = new BigtableTableAdminClient();
        self::$instanceId = self::requireEnv("BIGTABLE_INSTANCE");
        self::$tableId = uniqid(self::TABLE_ID_PREFIX);
    }

    public function setUp()
    {
        $this->useResourceExhaustedBackoff();

        $formattedParent = self::$bigtableTableAdminClient
            ->instanceName(self::$projectId, self::$instanceId);
        $table = new Table();
        self::$tableAdminClient->createtable(
            $formattedParent,
            self::$tableId,
            $table
        )->setColumnFamilies(["stats_summary"]);
    }

//
//    public function testWriteSimple()
//    {
//        $output = $this->runSnippet('writes/write_simple', [
//            self::$projectId,
//            self::$instanceId,
//            self::$tableId
//        ]);
//
//        $this->assertContains('Successfully wrote row.', $output);
//    }
}