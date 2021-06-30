<?php
/**
 * Copyright 2018 Google Inc.
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

namespace Google\Cloud\Samples\Storage;

use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for Bucket Lock.
 */
class BucketLockTest extends TestCase
{
    use TestTrait;

    private static $bucketName;
    protected $storage;
    protected $bucket;
    protected $object;

    public function setUp(): void
    {
        // Sleep to avoid the rate limit for creating/deleting.
        sleep(5 + rand(2, 4));
        $this->storage = new StorageClient();
        // Append random because tests for multiple PHP versions were running at the same time.
        self::$bucketName = 'php-bucket-lock-' . time() . '-' . rand(1000, 9999);
        $this->bucket = $this->storage->createBucket(self::$bucketName);
    }

    public function tearDown(): void
    {
        $this->object && $this->object->delete();
        $this->bucket->delete();
    }

    public function uploadObject()
    {
        $objectName = 'test-object-' . time();
        $file = tempnam(sys_get_temp_dir(), '/tests');
        file_put_contents($file, 'foo' . rand());
        $this->object = $this->bucket->upload($file, [
            'name' => $objectName
        ]);
        $this->object->reload();
    }

    public function testRetentionPolicyNoLock()
    {
        $retentionPeriod = 5;
        $output = self::runFunctionSnippet('set_retention_policy', [
            self::$bucketName,
            $retentionPeriod,
        ]);

        $this->assertStringContainsString(
            sprintf('Bucket %s retention period set to %d seconds' . PHP_EOL, self::$bucketName, $retentionPeriod),
            $output
        );

        $this->bucket->reload();
        $effectiveTime = $this->bucket->info()['retentionPolicy']['effectiveTime'];

        $this->assertFalse(array_key_exists('isLocked',
            $this->bucket->info()['retentionPolicy']));
        $this->assertNotNull($effectiveTime);
        $this->assertEquals($this->bucket->info()['retentionPolicy']['retentionPeriod'], $retentionPeriod);

        $output = self::runFunctionSnippet('get_retention_policy', [
            self::$bucketName,
        ]);

        $this->assertStringContainsString(
            'Retention Policy for ' . self::$bucketName,
            $output
        );

        $this->assertStringContainsString(
            'Retention Period: ' . $retentionPeriod,
            $output
        );

        $this->assertStringContainsString($effectiveTime, $output);

        $this->uploadObject();
        $this->assertNotNull($this->object->info()['retentionExpirationTime']);

        $output = self::runFunctionSnippet('remove_retention_policy', [
            self::$bucketName,
        ]);

        $this->assertStringContainsString(
            sprintf('Removed bucket %s retention policy', self::$bucketName),
            $output
        );

        $this->bucket->reload();

        $this->assertFalse(array_key_exists('retentionPolicy', $this->bucket->info()));

        sleep($retentionPeriod);
    }

    public function testRetentionPolicyLock()
    {
        $retentionPeriod = 5;
        $output = self::runFunctionSnippet('set_retention_policy', [
            self::$bucketName,
            $retentionPeriod,
        ]);

        $this->assertStringContainsString(
            sprintf('Bucket %s retention period set to %d seconds' . PHP_EOL, self::$bucketName, $retentionPeriod),
            $output
        );

        $this->bucket->reload();

        $this->assertFalse(array_key_exists(
            'isLocked',
            $this->bucket->info()['retentionPolicy']
        ));

        $output = self::runFunctionSnippet('lock_retention_policy', [
            self::$bucketName,
        ]);

        $this->assertStringContainsString(
            sprintf('Bucket %s retention policy locked', self::$bucketName),
            $output
        );

        $output = self::runFunctionSnippet('get_retention_policy', [
            self::$bucketName,
        ]);

        $this->assertStringContainsString(
            'Retention Policy is locked',
            $output
        );
    }

    public function testEnableDisableGetDefaultEventBasedHold()
    {
        $output = self::runFunctionSnippet('enable_default_event_based_hold', [
            $this->bucket->name(),
        ]);

        $this->assertStringContainsString(
            "Default event-based hold was enabled for {$this->bucket->name()}",
            $output
        );

        $this->bucket->reload();

        $this->assertTrue($this->bucket->info()['defaultEventBasedHold']);

        $output = self::runFunctionSnippet('get_default_event_based_hold', [
            $this->bucket->name(),
        ]);

        $this->assertStringContainsString(
            "Default event-based hold is enabled for {$this->bucket->name()}",
            $output
        );

        $this->uploadObject();
        $this->assertTrue($this->object->info()['eventBasedHold']);

        $output = self::runFunctionSnippet('release_event_based_hold', [
            $this->bucket->name(),
            $this->object->name(),
        ]);

        $this->assertStringContainsString(
            "Event-based hold was released for {$this->object->name()}",
            $output
        );

        $this->object->reload();
        $this->assertFalse($this->object->info()['eventBasedHold']);

        $output = self::runFunctionSnippet('disable_default_event_based_hold', [
            $this->bucket->name(),
        ]);

        $this->assertStringContainsString(
            "Default event-based hold was disabled for {$this->bucket->name()}",
            $output
        );

        $this->bucket->reload();
        $this->assertFalse($this->bucket->info()['defaultEventBasedHold']);

        $output = self::runFunctionSnippet('get_default_event_based_hold', [
            $this->bucket->name(),
        ]);

        $this->assertStringContainsString(
            "Default event-based hold is not enabled for {$this->bucket->name()}",
            $output
        );
    }

    public function testEnableDisableEventBasedHold()
    {
        $this->uploadObject();

        $this->assertFalse(array_key_exists('eventBasedHold', $this->object->info()));

        $output = self::runFunctionSnippet('set_event_based_hold', [
            $this->bucket->name(),
            $this->object->name(),
        ]);

        $this->assertStringContainsString(
            "Event-based hold was set for {$this->object->name()}",
            $output
        );

        $this->object->reload();
        $this->assertTrue($this->object->info()['eventBasedHold']);

        $output = self::runFunctionSnippet('release_event_based_hold', [
            $this->bucket->name(),
            $this->object->name(),
        ]);

        $this->assertStringContainsString(
            "Event-based hold was released for {$this->object->name()}",
            $output
        );

        $this->object->reload();
        $this->assertFalse($this->object->info()['eventBasedHold']);
    }

    public function testEnableDisableTemporaryHold()
    {
        $this->uploadObject();
        $this->assertFalse(array_key_exists('temporaryHold', $this->object->info()));

        $output = self::runFunctionSnippet('set_temporary_hold', [
            $this->bucket->name(),
            $this->object->name(),
        ]);

        $this->assertStringContainsString(
            "Temporary hold was set for {$this->object->name()}",
            $output
        );

        $this->object->reload();
        $this->assertTrue($this->object->info()['temporaryHold']);

        $output = self::runFunctionSnippet('release_temporary_hold', [
            $this->bucket->name(),
            $this->object->name(),
        ]);

        $this->assertStringContainsString(
            "Temporary hold was released for {$this->object->name()}",
            $output
        );

        $this->object->reload();
        $this->assertFalse($this->object->info()['temporaryHold']);
    }
}
