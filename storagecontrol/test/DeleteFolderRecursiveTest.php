<?php
/**
 * Copyright 2026 Google LLC
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

namespace Google\Cloud\Samples\StorageControl;

use Google\Cloud\Storage\Control\V2\Client\StorageControlClient;
use Google\Cloud\Storage\Control\V2\CreateFolderRequest;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Tests for storage control library samples.
 */
class DeleteFolderRecursiveTest extends TestCase
{
    use TestTrait;

    private static $sourceBucket;
    private static $folderId;
    private static $folderName;
    private static $storage;
    private static $storageControlClient;
    private static $location;

    public static function setUpBeforeClass(): void
    {
        self::checkProjectEnvVars();
        self::$storage = new StorageClient();
        self::$storageControlClient = new StorageControlClient();
        self::$location = 'us-west1';
        $uniqueBucketId = time() . rand();
        self::$folderId = time() . rand();
        self::$sourceBucket = self::$storage->createBucket(
            sprintf('php-gcscontrol-sample-%s', $uniqueBucketId),
            [
                'location' => self::$location,
                'hierarchicalNamespace' => ['enabled' => true],
                'iamConfiguration' => ['uniformBucketLevelAccess' => ['enabled' => true]]
            ]
        );
        self::$folderName = self::$storageControlClient->folderName(
            '_',
            self::$sourceBucket->name(),
            self::$folderId
        );
    }

    public static function tearDownAfterClass(): void
    {
        foreach (self::$sourceBucket->objects(['versions' => true]) as $object) {
            $object->delete();
        }
        self::$sourceBucket->delete();
    }

    public function testDeleteFolderRecursive()
    {
        // First create a folder
        $request = (new CreateFolderRequest())
            ->setParent(self::$storageControlClient->bucketName('_', self::$sourceBucket->name()))
            ->setFolderId(self::$folderId);
        self::$storageControlClient->createFolder($request);

        $output = $this->runFunctionSnippet('delete_folder_recursive', [
            self::$sourceBucket->name(), self::$folderId
        ]);

        $this->assertStringContainsString(
            sprintf('Deleted folder: %s', self::$folderId),
            $output
        );
    }
}
