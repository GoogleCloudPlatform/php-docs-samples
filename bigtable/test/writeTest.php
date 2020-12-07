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

use PHPUnit\Framework\TestCase;

final class WriteTest extends TestCase
{
    use BigtableTestTrait;

    const INSTANCE_ID_PREFIX = 'phpunit-test-';
    const TABLE_ID_PREFIX = 'mobile-time-series-';

    public static function setUpBeforeClass(): void
    {
        self::setUpBigtableVars();
        self::$instanceId = self::createDevInstance(self::INSTANCE_ID_PREFIX);
        self::$tableId = self::createTable(self::TABLE_ID_PREFIX);
    }

    public function setUp(): void
    {
        $this->useResourceExhaustedBackoff();
    }

    public static function tearDownAfterClass(): void
    {
        self::deleteBigtableInstance();
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
