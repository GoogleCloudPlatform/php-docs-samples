<?php
/**
 * Copyright 2024 Google Inc.
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
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Tests for storage control library samples.
 */
class StorageControlTest extends TestCase
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

    public function testCreateFolder()
    {
        $output = $this->runFunctionSnippet('create_folder', [
            self::$sourceBucket->name(), self::$folderId
        ]);

        $this->assertStringContainsString(
            sprintf('Created folder: %s', self::$folderName),
            $output
        );
    }

    /**
     * @depends testCreateFolder
     */
    public function testGetFolder()
    {
        $output = $this->runFunctionSnippet('get_folder', [
            self::$sourceBucket->name(), self::$folderId
        ]);

        $this->assertStringContainsString(
            self::$folderName,
            $output
        );
    }

    /**
     * @depends testGetFolder
     */
    public function testListFolders()
    {
        $output = $this->runFunctionSnippet('list_folders', [
            self::$sourceBucket->name()
        ]);

        $this->assertStringContainsString(
            self::$folderName,
            $output
        );
    }

    /**
     * @depends testListFolders
     */
    public function testRenameFolder()
    {
        $newFolderId = time() . rand();
        $output = $this->runFunctionSnippet('rename_folder', [
            self::$sourceBucket->name(), self::$folderId, $newFolderId
        ]);

        $this->assertStringContainsString(
            sprintf('Renamed folder %s to %s', self::$folderId, $newFolderId),
            $output
        );

        self::$folderId = $newFolderId;
    }

    /**
     * @depends testRenameFolder
     */
    public function testDeleteFolder()
    {
        $output = $this->runFunctionSnippet('delete_folder', [
            self::$sourceBucket->name(), self::$folderId
        ]);

        $this->assertStringContainsString(
            sprintf('Deleted folder: %s', self::$folderId),
            $output
        );
    }
}
