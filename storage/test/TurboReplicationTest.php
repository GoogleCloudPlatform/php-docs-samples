<?php
/**
 * Copyright 2021 Google LLC
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

namespace Google\Cloud\Samples\Storage\Tests;

use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Turbo Replication(RPO)
 */
class TurboReplicationTest extends TestCase
{
    use TestTrait;

    private static $storage;
    private static $bucketName;
    private static $bucket;

    public static function setUpBeforeClass(): void
    {
        self::$storage = new StorageClient();
        self::$bucketName = uniqid('samples-turbo-replication-');
    }

    public static function tearDownAfterClass(): void
    {
        self::$bucket->delete();
    }

    public function testCreateBucketWithTurboReplication()
    {
        $output = self::runFunctionSnippet('create_bucket_turbo_replication', [
            self::$bucketName,
            'asia1'
        ]);

        $this->assertStringContainsString(
            sprintf(
                'Bucket with Turbo Replication set to \'ASYNC_TURBO\' created: %s',
                self::$bucketName
            ),
            $output
        );

        self::$bucket = self::$storage->bucket(self::$bucketName); 
        $this->assertEquals(Bucket::RPO_ASYNC_TYRBO, self::$bucket->rpo());
    }

    /** @depends testCreateBucketWithTurboReplication */
    public function testGetTurboReplicationStatus()
    {
        $output = self::runFunctionSnippet('get_turbo_replication_status', [
            self::$bucketName,
        ]);

        $this->assertEquals(
            sprintf(
                'The bucket\'s RPO value is: %s.' . PHP_EOL,
                Bucket::RPO_ASYNC_TYRBO
            ),
            $output
        );
    }

    /** @depends testCreateBucketWithTurboReplication */
    public function testSetTurboReplicationStatusDefault()
    {
        $output = self::runFunctionSnippet('set_turbo_replication_default', [
            self::$bucketName,
        ]);

        $this->assertEquals(
            sprintf(
                'Turbo Replication has been set to DEFAULT for %s.' . PHP_EOL,
                self::$bucketName
            ),
            $output
        );

        self::$bucket->reload();
        $this->assertEquals(Bucket::RPO_DEFAULT, self::$bucket->rpo());
    }

    /** @depends testCreateBucketWithTurboReplication */
    public function testSetTurboReplicationStatusAsyncTurbo()
    {
        $output = self::runFunctionSnippet('set_turbo_replication_async_turbo', [
            self::$bucketName,
        ]);

        $this->assertEquals(
            sprintf(
                'Turbo Replication has been set to ASYNC_TURBO for %s.' . PHP_EOL,
                self::$bucketName
            ),
            $output
        );

        self::$bucket->reload();
        $this->assertEquals(Bucket::RPO_ASYNC_TYRBO, self::$bucket->rpo());
    }
}
