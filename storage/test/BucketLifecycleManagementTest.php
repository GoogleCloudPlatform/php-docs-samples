<?php
/**
 * Copyright 2020 Google LLC.
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
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for enable/disable bucket lifecycle management.
 */
class BucketLifecycleManagementTest extends TestCase
{
    use TestTrait;
    use ExecuteCommandTrait;

    protected $bucket;

    public function setUp(): void
    {
        $storage = new StorageClient();
        $bucketName = sprintf('php-olm-%s-%s', time(), rand(1000, 9999));
        $this->useResourceExhaustedBackoff();
        self::$backoff->execute(function () use ($storage, $bucketName) {
            $this->bucket = $storage->createBucket($bucketName);
        });
    }

    public function tearDown(): void
    {
        $this->bucket->delete();
    }

    public function testEnableBucketLifecycleManagement()
    {
        $bucketName = $this->bucket->name();
        $output = $this->runFunctionSnippet('enable_bucket_lifecycle_management', [
            $bucketName,
        ]);
        $match = "Lifecycle management is enabled for bucket $bucketName and the rules are:";
        $this->assertStringContainsString($match, $output);
        $this->bucket->reload();
        $lifecycle = $this->bucket->currentLifecycle()->toArray();
        $rules = $lifecycle['rule'];
        $this->assertContains([
            'action' => [
                'type' => 'Delete'
            ],
            'condition' => [
                'age' => 100
            ]
        ], $rules);
    }

    /** @depends testEnableBucketLifecycleManagement */
    public function testDisableBucketLifecycleManagement()
    {
        $bucketName = $this->bucket->name();
        $output = $this->runFunctionSnippet('disable_bucket_lifecycle_management', [
            $bucketName,
        ]);

        $expectedOutput = "Lifecycle management is disabled for bucket $bucketName.\n";
        $this->assertEquals($expectedOutput, $output);
        $this->bucket->reload();
        $lifecycle = $this->bucket->currentLifecycle()->toArray();
        $this->assertEmpty($lifecycle);
    }
}
