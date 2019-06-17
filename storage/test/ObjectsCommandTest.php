<?php
/**
 * Copyright 2016 Google Inc.
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

use Google\Cloud\Samples\Storage\ObjectsCommand;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for ObjectsCommand.
 */
class ObjectsCommandTest extends TestCase
{
    use TestTrait;

    protected $commandTester;
    protected $storage;

    public function setUp()
    {
        $application = require __DIR__ . '/../storage.php';
        $this->commandTester = new CommandTester($application->get('objects'));
        $this->storage = new StorageClient();
    }

    public function testListObjects()
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex("/Object:/");
    }

    public function testListObjectsWithPrefix()
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');
        $objectName = $this->requireEnv('GOOGLE_STORAGE_OBJECT');

        ob_start();
        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                '--prefix' => $objectName,
            ],
            ['interactive' => false]
        );
        $output = ob_get_clean();

        $this->assertGreaterThan(0, substr_count($output, 'Object: '));
    }

    public function testManageObject()
    {
        $bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');

        $objectName = 'test-object-' . time();
        $bucket = $this->storage->bucket($bucketName);
        $object = $bucket->object($objectName);
        $uploadFrom = tempnam(sys_get_temp_dir(), '/tests');
        $basename = basename($uploadFrom);
        file_put_contents($uploadFrom, 'foo' . rand());
        $downloadTo = tempnam(sys_get_temp_dir(), '/tests');
        $downloadToBasename = basename($downloadTo);

        $this->assertFalse($object->exists());

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                'object' => $objectName,
                '--upload-from' => $uploadFrom,
            ],
            ['interactive' => false]
        );

        $object->reload();
        $this->assertTrue($object->exists());

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                'object' => $objectName,
                '--copy-to' => $objectName . '-copy',
            ],
            ['interactive' => false]
        );

        $copyObject = $bucket->object($objectName . '-copy');
        $this->assertTrue($copyObject->exists());

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                'object' => $objectName . '-copy',
                '--delete' => true,
            ],
            ['interactive' => false]
        );

        $this->assertFalse($copyObject->exists());

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                'object' => $objectName,
                '--make-public' => true,
            ],
            ['interactive' => false]
        );

        $acl = $object->acl()->get(['entity' => 'allUsers']);
        $this->assertArrayHasKey('role', $acl);
        $this->assertEquals('READER', $acl['role']);

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                'object' => $objectName,
                '--download-to' => $downloadTo,
            ],
            ['interactive' => false]
        );

        $this->assertTrue(file_exists($downloadTo));

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                'object' => $objectName,
                '--move-to' => $objectName . '-moved',
            ],
            ['interactive' => false]
        );

        $this->assertFalse($object->exists());
        $movedObject = $bucket->object($objectName . '-moved');
        $this->assertTrue($movedObject->exists());

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                'object' => $objectName . '-moved',
                '--delete' => true,
            ],
            ['interactive' => false]
        );

        $this->assertFalse($movedObject->exists());

        // $bucketUrl = sprintf('gs://%s', $bucketName);
        $objectUrl = sprintf('gs://%s/%s', $bucketName, $objectName);
        $outputString = <<<EOF
Uploaded $basename to $objectUrl
Copied $objectUrl to $objectUrl-copy
Deleted $objectUrl-copy
$objectUrl is now public
Downloaded $objectUrl to $downloadToBasename
Moved $objectUrl to $objectUrl-moved
Deleted $objectUrl-moved

EOF;
        $this->expectOutputString($outputString);
    }
}
