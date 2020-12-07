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

use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for ObjectsCommand.
 */
class ObjectsCommandTest extends TestCase
{
    use TestTrait, ExecuteCommandTrait;

    private static $bucketName;
    private static $storage;
    private static $commandFile = __DIR__ . '/../storage.php';

    public static function setUpBeforeClass()
    {
        self::$bucketName = sprintf(
            '%s-legacy',
            self::requireEnv('GOOGLE_STORAGE_BUCKET')
        );
        self::$storage = new StorageClient();
    }

    public function testListObjects()
    {
        $output = $this->runCommand('objects', [
            'bucket' => self::$bucketName,
        ]);

        $this->assertContains('Object:', $output);
    }

    public function testListObjectsWithPrefix()
    {
        $objectName = $this->requireEnv('GOOGLE_STORAGE_OBJECT');

        $output = $this->runCommand('objects', [
            'bucket' => self::$bucketName,
            '--prefix' => $objectName,
        ]);

        $this->assertContains('Object:', $output);
    }

    public function testManageObject()
    {
        $objectName = 'test-object-' . time();
        $bucket = self::$storage->bucket(self::$bucketName);
        $object = $bucket->object($objectName);
        $uploadFrom = tempnam(sys_get_temp_dir(), '/tests');
        $basename = basename($uploadFrom);
        file_put_contents($uploadFrom, 'foo' . rand());
        $downloadTo = tempnam(sys_get_temp_dir(), '/tests');
        $downloadToBasename = basename($downloadTo);

        $this->assertFalse($object->exists());

        $output = $this->runCommand('objects', [
            'bucket' => self::$bucketName,
            'object' => $objectName,
            '--upload-from' => $uploadFrom,
        ]);

        $object->reload();
        $this->assertTrue($object->exists());

        $output .= $this->runCommand('objects', [
            'bucket' => self::$bucketName,
            'object' => $objectName,
            '--copy-to' => $objectName . '-copy',
        ]);

        $copyObject = $bucket->object($objectName . '-copy');
        $this->assertTrue($copyObject->exists());

        $output .= $this->runCommand('objects', [
            'bucket' => self::$bucketName,
            'object' => $objectName . '-copy',
            '--delete' => true,
        ]);

        $this->assertFalse($copyObject->exists());

        $output .= $this->runCommand('objects', [
            'bucket' => self::$bucketName,
            'object' => $objectName,
            '--make-public' => true,
        ]);

        $acl = $object->acl()->get(['entity' => 'allUsers']);
        $this->assertArrayHasKey('role', $acl);
        $this->assertEquals('READER', $acl['role']);

        $output .= $this->runCommand('objects', [
            'bucket' => self::$bucketName,
            'object' => $objectName,
            '--download-to' => $downloadTo,
        ]);

        $this->assertTrue(file_exists($downloadTo));

        $output .= $this->runCommand('objects', [
            'bucket' => self::$bucketName,
            'object' => $objectName,
            '--move-to' => $objectName . '-moved',
        ]);

        $this->assertFalse($object->exists());
        $movedObject = $bucket->object($objectName . '-moved');
        $this->assertTrue($movedObject->exists());

        $output .= $this->runCommand('objects', [
            'bucket' => self::$bucketName,
            'object' => $objectName . '-moved',
            '--delete' => true,
        ]);

        $this->assertFalse($movedObject->exists());

        $objectUrl = sprintf('gs://%s/%s', self::$bucketName, $objectName);
        $outputString = <<<EOF
Uploaded $basename to $objectUrl
Copied $objectUrl to $objectUrl-copy
Deleted $objectUrl-copy
$objectUrl is now public
Downloaded $objectUrl to $downloadToBasename
Moved $objectUrl to $objectUrl-moved
Deleted $objectUrl-moved

EOF;
        $this->assertEquals($output, $outputString);
    }
}
