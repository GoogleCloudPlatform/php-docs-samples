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

namespace Google\Cloud\Samples\Storage\Tests;

use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for BucketLockCommand.
 */
class BucketLockCommandTest extends TestCase
{
    use TestTrait;

    protected $commandTester;
    protected $storage;
    protected $bucket;
    protected $object;

    public function setUp()
    {
        // Sleep to avoid the rate limit for creating/deleting.
        sleep(5 + rand(2, 4));
        $application = require __DIR__ . '/../storage.php';
        $this->commandTester = new CommandTester($application->get('bucket-lock'));
        $this->storage = new StorageClient();
        // Append random because tests for multiple PHP versions were running at the same time.
        $bucketName = 'php-bucket-lock-' . time() . '-' . rand(1000, 9999);
        $this->bucket = $this->storage->createBucket($bucketName);
    }

    public function tearDown()
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
        $this->commandTester->execute(
            [
                'bucket' => $this->bucket->name(),
                'retention-period' => $retentionPeriod,
                '--set-retention-policy' => true,
            ],
            ['interactive' => false]
        );
        $this->bucket->reload();
        $effectiveTime = $this->bucket->info()['retentionPolicy']['effectiveTime'];

        $this->assertFalse(array_key_exists('isLocked',
            $this->bucket->info()['retentionPolicy']));
        $this->assertNotNull($effectiveTime);
        $this->assertEquals($this->bucket->info()['retentionPolicy']['retentionPeriod'], $retentionPeriod);

        $this->commandTester->execute(
            [
                'bucket' => $this->bucket->name(),
                '--get-retention-policy' => true,
            ],
            ['interactive' => false]
        );

        $this->uploadObject();
        $this->assertNotNull($this->object->info()['retentionExpirationTime']);

        $this->commandTester->execute(
            [
                'bucket' => $this->bucket->name(),
                '--remove-retention-policy' => true,
            ],
            ['interactive' => false]
        );
        $this->bucket->reload();

        $this->assertFalse(array_key_exists('retentionPolicy', $this->bucket->info()));

        $outputString = <<<EOF
Bucket {$this->bucket->name()} retention period set for $retentionPeriod seconds
Retention Policy for {$this->bucket->name()}
Retention Period: 5
Effective Time: $effectiveTime
Removed bucket {$this->bucket->name()} retention policy

EOF;
        $this->expectOutputString($outputString);
        sleep($retentionPeriod);
    }

    public function testRetentionPolicyLock()
    {
        $retentionPeriod = 5;
        $this->commandTester->execute(
            [
                'bucket' => $this->bucket->name(),
                'retention-period' => $retentionPeriod,
                '--set-retention-policy' => true,
            ],
            ['interactive' => false]
        );
        $this->bucket->reload();

        $this->assertFalse(array_key_exists('isLocked',
            $this->bucket->info()['retentionPolicy']));

        $this->commandTester->execute(
            [
                'bucket' => $this->bucket->name(),
                '--lock-retention-policy' => true,
            ],
            ['interactive' => false]
        );
        $this->bucket->reload();

        $this->assertTrue($this->bucket->info()['retentionPolicy']['isLocked']);

        $outputString = <<<EOF
Bucket {$this->bucket->name()} retention period set for $retentionPeriod seconds
Bucket {$this->bucket->name()} retention policy locked

EOF;
        $this->expectOutputString($outputString);
    }

    public function testEnableDisableGetDefaultEventBasedHold()
    {
        $this->commandTester->execute(
            [
                'bucket' => $this->bucket->name(),
                '--enable-default-event-based-hold' => true,
            ],
            ['interactive' => false]
        );
        $this->bucket->reload();

        $this->assertTrue($this->bucket->info()['defaultEventBasedHold']);

        $this->commandTester->execute(
            [
                'bucket' => $this->bucket->name(),
                '--get-default-event-based-hold' => true,
            ],
            ['interactive' => false]
        );

        $this->uploadObject();
        $this->assertTrue($this->object->info()['eventBasedHold']);

        $this->commandTester->execute(
            [
                'bucket' => $this->bucket->name(),
                'object' => $this->object->name(),
                '--release-event-based-hold' => true,
            ],
            ['interactive' => false]
        );
        $this->object->reload();
        $this->assertFalse($this->object->info()['eventBasedHold']);

        $this->commandTester->execute(
            [
                'bucket' => $this->bucket->name(),
                '--disable-default-event-based-hold' => true,
            ],
            ['interactive' => false]
        );
        $this->bucket->reload();
        $this->assertFalse($this->bucket->info()['defaultEventBasedHold']);

        $this->commandTester->execute(
            [
                'bucket' => $this->bucket->name(),
                '--get-default-event-based-hold' => true,
            ],
            ['interactive' => false]
        );

        $outputString = <<<EOF
Default event-based hold was enabled for {$this->bucket->name()}
Default event-based hold is enabled for {$this->bucket->name()}
Event-based hold was released for {$this->object->name()}
Default event-based hold was disabled for {$this->bucket->name()}
Default event-based hold is not enabled for {$this->bucket->name()}

EOF;
        $this->expectOutputString($outputString);
    }

    public function testEnableDisableEventBasedHold()
    {
        $this->uploadObject();

        $this->assertFalse(array_key_exists('eventBasedHold', $this->object->info()));

        $this->commandTester->execute(
            [
                'bucket' => $this->bucket->name(),
                'object' => $this->object->name(),
                '--set-event-based-hold' => true,
            ],
            ['interactive' => false]
        );
        $this->object->reload();
        $this->assertTrue($this->object->info()['eventBasedHold']);

        $this->commandTester->execute(
            [
                'bucket' => $this->bucket->name(),
                'object' => $this->object->name(),
                '--release-event-based-hold' => true,
            ],
            ['interactive' => false]
        );
        $this->object->reload();
        $this->assertFalse($this->object->info()['eventBasedHold']);

        $outputString = <<<EOF
Event-based hold was set for {$this->object->name()}
Event-based hold was released for {$this->object->name()}

EOF;
        $this->expectOutputString($outputString);
    }

    public function testEnableDisableTemporaryHold()
    {
        $this->uploadObject();
        $this->assertFalse(array_key_exists('temporaryHold', $this->object->info()));

        $this->commandTester->execute(
            [
                'bucket' => $this->bucket->name(),
                'object' => $this->object->name(),
                '--set-temporary-hold' => true,
            ],
            ['interactive' => false]
        );
        $this->object->reload();
        $this->assertTrue($this->object->info()['temporaryHold']);

        $this->commandTester->execute(
            [
                'bucket' => $this->bucket->name(),
                'object' => $this->object->name(),
                '--release-temporary-hold' => true,
            ],
            ['interactive' => false]
        );
        $this->object->reload();
        $this->assertFalse($this->object->info()['temporaryHold']);

        $outputString = <<<EOF
Temporary hold was set for {$this->object->name()}
Temporary hold was released for {$this->object->name()}

EOF;
        $this->expectOutputString($outputString);
    }
}
