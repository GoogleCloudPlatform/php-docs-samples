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
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for storage objects.
 */
class ObjectsTest extends TestCase
{
    use TestTrait;

    private static $bucketName;
    private static $storage;
    private static $contents;

    public static function setUpBeforeClass(): void
    {
        self::$bucketName = getenv('GOOGLE_STORAGE_BUCKET_LEGACY') ?: sprintf(
            '%s-legacy',
            self::requireEnv('GOOGLE_STORAGE_BUCKET')
        );
        self::$storage = new StorageClient();
        self::$contents = ' !"#$%&\'()*,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[]^_`abcdefghijklmnopqrstuvwxyz{|}~';
    }

    public function testObjectContexts()
    {
        $objectContextsBucket = self::$storage->createBucket(
            sprintf('%s-test-bucket-%s', self::$projectId, uniqid())
        );
        $bucketName = $objectContextsBucket->name();
        $objectName = 'object-contexts-' . uniqid();
        $object = $objectContextsBucket->upload('test', [
            'name' => $objectName,
        ]);
        $objectInfo = $object->info()['name'];

        // Set Object Contexts
        $setOutput = $this->runFunctionSnippet('set_object_contexts', [
            $bucketName,
            $objectInfo,
        ]);

        $this->assertStringContainsString("Contexts for object $objectInfo were updated", $setOutput);

        // Get Object Contexts
        $getOutput = $this->runFunctionSnippet('get_object_contexts', [
            $bucketName,
            $objectInfo
        ]);

        $this->assertStringContainsString('Key: department, Value: finance', $getOutput);
        $this->assertStringContainsString('Key: priority, Value: high', $getOutput);

        // List Object Contexts using filter
        $listOutput = $this->runFunctionSnippet('list_object_contexts', [
            $bucketName
        ]);
        $this->assertStringContainsString("Found object: $objectInfo", $listOutput);

        // Clear all contexts on the object.
        $object->update([
            'contexts' => [
                'custom' => null,
            ],
        ]);
        $object->reload();

        $clearedGetOutput = $this->runFunctionSnippet('get_object_contexts', [
            $bucketName,
            $objectName
        ]);

        $this->assertStringNotContainsString('Key: department, Value: finance', $clearedGetOutput);
        $this->assertStringNotContainsString('Key: priority, Value: high', $clearedGetOutput);

        $clearedInfo = $object->info();
        $this->assertTrue(empty($clearedInfo['contexts']['custom'] ?? []));

        // List again with filter after clearing contexts.
        $clearedListOutput = $this->runFunctionSnippet('list_object_contexts', [
            $bucketName
        ]);
        $this->assertEmpty(trim($clearedListOutput));
        $object->delete();
        $objectContextsBucket->delete();
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

    /**
      * @dataProvider provideMoveObject
      */
    public function testMoveObjectAtomic(bool $hnEnabled)
    {
        $bucketName = 'move-object-bucket-' . uniqid();
        $objectName = 'test-object-' . time();
        $newObjectName = $objectName . '-moved';
        $bucket = self::$storage->createBucket($bucketName, [
            'hierarchicalNamespace' => ['enabled' => $hnEnabled],
            'iamConfiguration' => ['uniformBucketLevelAccess' => ['enabled' => true]]
        ]);

        $object = $bucket->upload('test', ['name' => $objectName]);
        $this->assertTrue($object->exists());

        $output = self::runFunctionSnippet('move_object_atomic', [
            $bucketName,
            $objectName,
            $newObjectName
        ]);

        $this->assertEquals(
            sprintf(
                'Moved gs://%s/%s to gs://%s/%s' . PHP_EOL,
                $bucketName,
                $objectName,
                $bucketName,
                $newObjectName
            ),
            $output
        );

        $this->assertFalse($object->exists());
        $movedObject = $bucket->object($newObjectName);
        $this->assertTrue($movedObject->exists());

        $bucket->object($newObjectName)->delete();
        $bucket->delete();
    }

    public function provideMoveObject()
    {
        return [[true], [false]];
    }

    /**
     * @dataProvider provideCompose
     */
    public function testCompose(bool $deleteSourceObjects)
    {
        $bucket = self::$storage->bucket(self::$bucketName);
        $object1Name = uniqid('compose-object1-');
        $object2Name = uniqid('compose-object2-');
        $bucket->upload('content', ['name' => $object1Name]);
        $bucket->upload('content', ['name' => $object2Name]);

        $targetName = uniqid('compose-object-target-');

        $args = [
            self::$bucketName,
            $object1Name,
            $object2Name,
            $targetName,
        ];

        if ($deleteSourceObjects) {
            $args[] = true;
        }

        $output = self::runFunctionSnippet('compose_file', $args);

        $expectedOutput = sprintf(
            'New composite object %s was created by combining %s and %s',
            $targetName,
            $object1Name,
            $object2Name
        );

        if ($deleteSourceObjects) {
            $expectedOutput .= ' and source objects were deleted';
        }
        $expectedOutput .= PHP_EOL;

        $this->assertEquals($expectedOutput, $output);

        if ($deleteSourceObjects) {
            $this->assertFalse($bucket->object($object1Name)->exists());
            $this->assertFalse($bucket->object($object2Name)->exists());
        } else {
            $this->assertTrue($bucket->object($object1Name)->exists());
            $this->assertTrue($bucket->object($object2Name)->exists());
        }

        if (!$deleteSourceObjects) {
            $bucket->object($object1Name)->delete();
            $bucket->object($object2Name)->delete();
        }

        $bucket->object($targetName)->delete();
    }

    public function provideCompose()
    {
        return [[true], [false]];
    }

    public function testUploadAndDownloadObjectFromMemory()
    {
        $objectName = 'test-object-' . time();
        $bucket = self::$storage->bucket(self::$bucketName);
        $object = $bucket->object($objectName);

        $this->assertFalse($object->exists());

        $output = self::runFunctionSnippet('upload_object_from_memory', [
            self::$bucketName,
            $objectName,
            self::$contents,
        ]);

        $object->reload();
        $this->assertTrue($object->exists());

        $output = self::runFunctionSnippet('download_object_into_memory', [
            self::$bucketName,
            $objectName,
        ]);
        $this->assertStringContainsString(self::$contents, $output);
    }

    public function testUploadAndDownloadObjectStream()
    {
        $objectName = 'test-object-stream-' . time();
        // contents larger than atleast one chunk size
        $contents = str_repeat(self::$contents, 1024 * 10);
        $bucket = self::$storage->bucket(self::$bucketName);
        $object = $bucket->object($objectName);
        $this->assertFalse($object->exists());

        $output = self::runFunctionSnippet('upload_object_stream', [
            self::$bucketName,
            $objectName,
            $contents,
        ]);

        $object->reload();
        $this->assertTrue($object->exists());

        $output = self::runFunctionSnippet('download_object_into_memory', [
            self::$bucketName,
            $objectName,
        ]);
        $this->assertStringContainsString($contents, $output);
    }

    public function testDownloadByteRange()
    {
        $objectName = 'test-object-download-byte-range-' . time();
        $bucket = self::$storage->bucket(self::$bucketName);
        $object = $bucket->object($objectName);
        $downloadTo = tempnam(sys_get_temp_dir(), '/tests');
        $downloadToBasename = basename($downloadTo);
        $startPos = 1;
        $endPos = strlen(self::$contents) - 2;

        $this->assertFalse($object->exists());

        $output = self::runFunctionSnippet('upload_object_from_memory', [
            self::$bucketName,
            $objectName,
            self::$contents,
        ]);

        $object->reload();
        $this->assertTrue($object->exists());

        $output .= self::runFunctionSnippet('download_byte_range', [
            self::$bucketName,
            $objectName,
            $startPos,
            $endPos,
            $downloadTo,
        ]);

        $this->assertTrue(file_exists($downloadTo));
        $expectedContents = substr(self::$contents, $startPos, $endPos - $startPos + 1);
        $this->assertEquals($expectedContents, file_get_contents($downloadTo));
        $this->assertStringContainsString(
            sprintf(
                'Downloaded gs://%s/%s to %s',
                self::$bucketName,
                $objectName,
                $downloadToBasename,
            ),
            $output
        );
    }

    public function testChangeStorageClass()
    {
        $objectName = uniqid('change-storage-class-');

        $object = self::$storage->bucket(self::$bucketName)->upload('content', [
            'name' => $objectName,
        ]);

        $output = self::runFunctionSnippet('change_file_storage_class', [
            self::$bucketName,
            $objectName,
            'NEARLINE',
        ]);

        $this->assertEquals(
            sprintf(
                'Object %s in bucket %s had its storage class set to %s',
                $objectName,
                self::$bucketName,
                'NEARLINE'
            ),
            $output
        );

        $newObject = self::$storage->bucket(self::$bucketName)->object($objectName);
        $this->assertEquals('NEARLINE', $newObject->info()['storageClass']);
        $newObject->delete();
    }

    public function testSetMetadata()
    {
        $objectName = uniqid('set-metadata-');

        $object = self::$storage->bucket(self::$bucketName)->upload('content', [
            'name' => $objectName,
        ]);

        $output = self::runFunctionSnippet('set_metadata', [
            self::$bucketName,
            $objectName,
        ]);

        $this->assertEquals(
            sprintf(
                'Updated custom metadata for object %s in bucket %s',
                $objectName,
                self::$bucketName
            ),
            $output
        );

        $this->assertEquals('value', $object->reload()['metadata']['keyToAddOrUpdate']);
        $object->delete();
    }

    public function testGetMetadata()
    {
        $objectName = uniqid('set-metadata-');

        $content = 'content';
        $object = self::$storage->bucket(self::$bucketName)->upload($content, [
            'name' => $objectName,
        ]);

        $info = $object->reload();
        $output = self::runFunctionSnippet('object_metadata', [
            self::$bucketName,
            $object->name(),
        ]);

        $object->delete();

        $fields = [
            'Blob' => 'name',
            'Bucket' => 'bucket',
            'Storage class' => 'storageClass',
            'ID' => 'id',
            'Size' => 'size',
            'Updated' => 'updated',
            'Generation' => 'generation',
            'Metageneration' => 'metageneration',
            'Etag' => 'etag',
            'Crc32c' => 'crc32c',
            'MD5 Hash' => 'md5Hash',
        ];

        foreach ($fields as $key => $val) {
            $this->assertStringContainsString(
                sprintf('%s: %s', $key, $info[$val]),
                $output
            );
        }

        $this->assertStringNotContainsString('Temporary Hold', $output);
        $this->assertStringNotContainsString('Event-based hold', $output);
        $this->assertStringNotContainsString('Custom Time', $output);
        $this->assertStringNotContainsString('Retention Expiration Time', $output);
    }

    public function testListSoftDeletedObjects()
    {
        $bucket = self::$storage->bucket(self::$bucketName);
        $bucket->update([
            'softDeletePolicy' => [
                'retentionDuration' => 604800,
            ],
        ]);

        $objectName = uniqid('soft-deleted-object-');
        $object = $bucket->upload('content', ['name' => $objectName]);
        $object->delete();

        $output = self::runFunctionSnippet('list_soft_deleted_objects', [
            self::$bucketName,
        ]);

        $this->assertStringContainsString('Object:', $output);
    }

    public function testListSoftDeletedObjectVersions()
    {
        $bucket = self::$storage->bucket(self::$bucketName);
        $bucket->update([
            'softDeletePolicy' => [
                'retentionDuration' => 604800,
            ],
        ]);

        $objectName1 = 'soft-deleted-object-1';
        $object1 = $bucket->upload('content', ['name' => $objectName1]);
        $object1->delete();

        $objectName2 = 'soft-deleted-object-2';
        $object2 = $bucket->upload('content', ['name' => $objectName2]);
        $object2->delete();

        $output = self::runFunctionSnippet('list_soft_deleted_object_versions', [
            self::$bucketName,
            $objectName1
        ]);

        $this->assertStringContainsString($objectName1, $output);
        $this->assertStringNotContainsString($objectName2, $output);
    }

    public function testRestoreSoftDeletedObject()
    {
        $bucket = self::$storage->bucket(self::$bucketName);
        $bucket->update([
            'softDeletePolicy' => [
                'retentionDuration' => 60,
            ],
        ]);

        $objectName = uniqid('soft-deleted-object-');
        $object = $bucket->upload('content', ['name' => $objectName]);
        $info = $object->reload();
        $object->delete();

        $this->assertFalse($object->exists());

        $output = self::runFunctionSnippet('restore_soft_deleted_object', [
            self::$bucketName,
            $objectName,
            $info['generation']
        ]);

        $object = $bucket->object($objectName);
        $this->assertTrue($object->exists());
        $this->assertEquals(
            sprintf(
                'Soft deleted object %s was restored.' . PHP_EOL,
                $objectName
            ),
            $output
        );
    }
}
