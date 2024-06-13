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
    private static $managedFolderId;
    private static $folderName;
    private static $managedFolderName;
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
        self::$managedFolderId = time() . rand();
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
        self::$managedFolderName = self::$storageControlClient->managedFolderName(
            '_',
            self::$sourceBucket->name(),
            self::$managedFolderId
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

    public function testManagedCreateFolder()
    {
        $output = $this->runFunctionSnippet('managed_folder_create', [
            self::$sourceBucket->name(), self::$managedFolderId
        ]);

        $this->assertStringContainsString(
            sprintf('Performed createManagedFolder request for %s', self::$managedFolderName),
            $output
        );
    }

    /**
     * @depends testCreateFolder
     */
    public function testManagedGetFolder()
    {
        $output = $this->runFunctionSnippet('managed_folder_get', [
            self::$sourceBucket->name(), self::$managedFolderId
        ]);

        $this->assertStringContainsString(
            sprintf('Got Managed Folder %s', self::$managedFolderName),
            $output
        );
    }

    /**
     * @depends testManagedGetFolder
     */
    public function testManagedListFolders()
    {
        $output = $this->runFunctionSnippet('managed_folders_list', [
            self::$sourceBucket->name()
        ]);

        $this->assertStringContainsString(
            sprintf('%s bucket has managed folder %s', self::$sourceBucket->name(), self::$managedFolderName),
            $output
        );
    }

    /**
     * @depends testManagedListFolders
     */
    public function testManagedDeleteFolder()
    {
        $output = $this->runFunctionSnippet('managed_folder_delete', [
            self::$sourceBucket->name(), self::$managedFolderId
        ]);

        $this->assertStringContainsString(
            sprintf('Deleted Managed Folder %s', self::$managedFolderId),
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
