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
 * Unit Tests for BucketLockCommand.
 *
 * @group storage
 * @group storage-bucketlock
 */
class BucketLockCommandTest extends TestCase
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
            self::$projectId,
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
            self::$projectId,
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
            self::$projectId,
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
            self::$projectId,
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
            self::$projectId,
            self::$bucketName,
        ]);

        $this->assertStringContainsString(
            sprintf('Bucket %s retention policy locked', self::$bucketName),
            $output
        );

        $output = self::runFunctionSnippet('get_retention_policy', [
            self::$projectId,
            self::$bucketName,
        ]);

        $this->assertStringContainsString(
            'Retention Policy is locked',
            $output
        );
    }

//     public function testEnableDisableGetDefaultEventBasedHold()
//     {
//         $this->commandTester->execute(
//             [
//                 'bucket' => $this->bucket->name(),
//                 '--enable-default-event-based-hold' => true,
//             ],
//             ['interactive' => false]
//         );
//         $this->bucket->reload();

//         $this->assertTrue($this->bucket->info()['defaultEventBasedHold']);

//         $this->commandTester->execute(
//             [
//                 'bucket' => $this->bucket->name(),
//                 '--get-default-event-based-hold' => true,
//             ],
//             ['interactive' => false]
//         );

//         $this->uploadObject();
//         $this->assertTrue($this->object->info()['eventBasedHold']);

//         $this->commandTester->execute(
//             [
//                 'bucket' => $this->bucket->name(),
//                 'object' => $this->object->name(),
//                 '--release-event-based-hold' => true,
//             ],
//             ['interactive' => false]
//         );
//         $this->object->reload();
//         $this->assertFalse($this->object->info()['eventBasedHold']);

//         $this->commandTester->execute(
//             [
//                 'bucket' => $this->bucket->name(),
//                 '--disable-default-event-based-hold' => true,
//             ],
//             ['interactive' => false]
//         );
//         $this->bucket->reload();
//         $this->assertFalse($this->bucket->info()['defaultEventBasedHold']);

//         $this->commandTester->execute(
//             [
//                 'bucket' => $this->bucket->name(),
//                 '--get-default-event-based-hold' => true,
//             ],
//             ['interactive' => false]
//         );

//         $outputString = <<<EOF
// Default event-based hold was enabled for {$this->bucket->name()}
// Default event-based hold is enabled for {$this->bucket->name()}
// Event-based hold was released for {$this->object->name()}
// Default event-based hold was disabled for {$this->bucket->name()}
// Default event-based hold is not enabled for {$this->bucket->name()}

// EOF;
//         $this->expectOutputString($outputString);
//     }

//     public function testEnableDisableEventBasedHold()
//     {
//         $this->uploadObject();

//         $this->assertFalse(array_key_exists('eventBasedHold', $this->object->info()));

//         $this->commandTester->execute(
//             [
//                 'bucket' => $this->bucket->name(),
//                 'object' => $this->object->name(),
//                 '--set-event-based-hold' => true,
//             ],
//             ['interactive' => false]
//         );
//         $this->object->reload();
//         $this->assertTrue($this->object->info()['eventBasedHold']);

//         $this->commandTester->execute(
//             [
//                 'bucket' => $this->bucket->name(),
//                 'object' => $this->object->name(),
//                 '--release-event-based-hold' => true,
//             ],
//             ['interactive' => false]
//         );
//         $this->object->reload();
//         $this->assertFalse($this->object->info()['eventBasedHold']);

//         $outputString = <<<EOF
// Event-based hold was set for {$this->object->name()}
// Event-based hold was released for {$this->object->name()}

// EOF;
//         $this->expectOutputString($outputString);
//     }

//     public function testEnableDisableTemporaryHold()
//     {
//         $this->uploadObject();
//         $this->assertFalse(array_key_exists('temporaryHold', $this->object->info()));

//         $this->commandTester->execute(
//             [
//                 'bucket' => $this->bucket->name(),
//                 'object' => $this->object->name(),
//                 '--set-temporary-hold' => true,
//             ],
//             ['interactive' => false]
//         );
//         $this->object->reload();
//         $this->assertTrue($this->object->info()['temporaryHold']);

//         $this->commandTester->execute(
//             [
//                 'bucket' => $this->bucket->name(),
//                 'object' => $this->object->name(),
//                 '--release-temporary-hold' => true,
//             ],
//             ['interactive' => false]
//         );
//         $this->object->reload();
//         $this->assertFalse($this->object->info()['temporaryHold']);

//         $outputString = <<<EOF
// Temporary hold was set for {$this->object->name()}
// Temporary hold was released for {$this->object->name()}

// EOF;
//         $this->expectOutputString($outputString);
//     }
}
