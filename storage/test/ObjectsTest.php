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
 * Unit Tests for storage objects.
 *
 * @group storage-objects
 */
class ObjectsTest extends TestCase
{
    use TestTrait, ExecuteCommandTrait;

    private static $bucketName;
    private static $storage;

    public static function setUpBeforeClass(): void
    {
        self::$bucketName = getenv('GOOGLE_STORAGE_BUCKET_LEGACY') ?: sprintf(
            '%s-legacy',
            self::requireEnv('GOOGLE_STORAGE_BUCKET')
        );
        self::$storage = new StorageClient();
    }

    public function testListObjects()
    {
        $output = self::runFunctionSnippet('list_objects', [
            self::$bucketName,
        ]);

        $this->assertStringContainsString('Object:', $output);
    }

    public function testListObjectsWithPrefix()
    {
        $objectName = $this->requireEnv('GOOGLE_STORAGE_OBJECT');

        $output = self::runFunctionSnippet('list_objects_with_prefix', [
            self::$bucketName,
            $objectName,
        ]);

        $this->assertStringContainsString('Object:', $output);
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

        $output = self::runFunctionSnippet('upload_object', [
            self::$bucketName,
            $objectName,
            $uploadFrom,
        ]);

        $object->reload();
        $this->assertTrue($object->exists());

        $output .= self::runFunctionSnippet('copy_object', [
            self::$bucketName,
            $objectName,
            self::$bucketName,
            $objectName . '-copy',
        ]);

        $copyObject = $bucket->object($objectName . '-copy');
        $this->assertTrue($copyObject->exists());

        $output .= self::runFunctionSnippet('delete_object', [
            self::$bucketName,
            $objectName . '-copy',
        ]);

        $this->assertFalse($copyObject->exists());

        $output .= self::runFunctionSnippet('make_public', [
            self::$bucketName,
            $objectName,
        ]);

        $acl = $object->acl()->get(['entity' => 'allUsers']);
        $this->assertArrayHasKey('role', $acl);
        $this->assertEquals('READER', $acl['role']);

        $output .= self::runFunctionSnippet('download_object', [
            self::$bucketName,
            $objectName,
            $downloadTo,
        ]);

        $this->assertTrue(file_exists($downloadTo));

        $output .= self::runFunctionSnippet('move_object', [
            self::$bucketName,
            $objectName,
            self::$bucketName,
            $objectName . '-moved',
        ]);

        $this->assertFalse($object->exists());
        $movedObject = $bucket->object($objectName . '-moved');
        $this->assertTrue($movedObject->exists());

        $output .= self::runFunctionSnippet('delete_object', [
            self::$bucketName,
            $objectName . '-moved',
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
