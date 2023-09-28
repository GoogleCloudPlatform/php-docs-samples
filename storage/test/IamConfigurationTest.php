<?php
/**
 * Copyright 2019 Google Inc.
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
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for IamConfiguration.
 */
class IamConfigurationTest extends TestCase
{
    use TestTrait;

    protected $storage;
    protected $bucket;

    public function setUp(): void
    {
        // Sleep to avoid the rate limit for creating/deleting.
        sleep(5 + rand(2, 4));
        $this->storage = new StorageClient();

        // Append random because tests for multiple PHP versions were running at the same time.
        $bucketName = 'php-iamconfiguration-' . time() . '-' . rand(1000, 9999);
        $this->bucket = $this->storage->createBucket($bucketName);
    }

    public function tearDown(): void
    {
        $this->bucket->delete();
    }

    public function testEnableUniformBucketLevelAccess()
    {
        $output = self::runFunctionSnippet('enable_uniform_bucket_level_access', [
            $this->bucket->name(),
        ]);

        $outputString = <<<EOF
Uniform bucket-level access was enabled for {$this->bucket->name()}

EOF;
        $this->assertEquals($outputString, $output);
        $this->bucket->reload();
        $bucketInformation = $this->bucket->info();
        $ubla = $bucketInformation['iamConfiguration']['uniformBucketLevelAccess'];
        $this->assertTrue($ubla['enabled']);
    }

    /** @depends testEnableUniformBucketLevelAccess */
    public function testDisableUniformBucketLevelAccess()
    {
        $output = self::runFunctionSnippet('disable_uniform_bucket_level_access', [
            $this->bucket->name(),
        ]);

        $outputString = <<<EOF
Uniform bucket-level access was disabled for {$this->bucket->name()}

EOF;
        $this->assertEquals($outputString, $output);
        $this->bucket->reload();
        $bucketInformation = $this->bucket->info();
        $ubla = $bucketInformation['iamConfiguration']['uniformBucketLevelAccess'];
        $this->assertFalse($ubla['enabled']);
    }

    /** @depends testDisableUniformBucketLevelAccess */
    public function testGetUniformBucketLevelAccess()
    {
        $output = self::runFunctionSnippet('get_uniform_bucket_level_access', [
            $this->bucket->name(),
        ]);

        $outputString = <<<EOF
Uniform bucket-level access is disabled for {$this->bucket->name()}

EOF;
        $this->assertEquals($outputString, $output);
        $this->bucket->reload();
        $bucketInformation = $this->bucket->info();
        $ubla = $bucketInformation['iamConfiguration']['uniformBucketLevelAccess'];
        $this->assertFalse($ubla['enabled']);
    }
}
