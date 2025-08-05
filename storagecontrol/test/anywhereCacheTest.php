<?php

/**
 * Copyright 2025 Google Inc.
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
class anywhereCacheTest extends TestCase
{
    use TestTrait;

    private static $sourceBucket;
    private static $storage;
    private static $storageControlClient;
    private static $location;
    private static $zone;
    private static $cacheId;
    private static $anywhereCacheName;

    public static function setUpBeforeClass(): void
    {
        self::checkProjectEnvVars();
        self::$storage = new StorageClient();
        self::$storageControlClient = new StorageControlClient();
        self::$location = 'us-west1';
        self::$zone = 'us-west1-b';
        $uniqueBucketId = time() . rand();
        self::$cacheId = self::$zone;
        self::$sourceBucket = self::$storage->createBucket(
            sprintf('php-gcscontrol-sample-%s', $uniqueBucketId),
            [
                'location' => self::$location,
                'hierarchicalNamespace' => ['enabled' => true],
                'iamConfiguration' => ['uniformBucketLevelAccess' => ['enabled' => true]]
            ]
        );
        self::$anywhereCacheName = self::$storageControlClient->anywhereCacheName(
            '_', // Set project to "_" to signify global bucket
            self::$sourceBucket->name(),
            self::$cacheId
        );
    }

    public static function tearDownAfterClass(): void
    {
        foreach (self::$sourceBucket->objects(['versions' => true]) as $object) {
            $object->delete();
        }
        self::$sourceBucket->delete();
    }

    public function testCreateAnywhereCache()
    {
        $output = $this->runFunctionSnippet('create_anywhere_cache', [
            self::$sourceBucket->name(),
            self::$zone,
        ]);

        $this->assertStringContainsString(
            sprintf('Created anywhere cache: %s', self::$anywhereCacheName),
            $output
        );
    }

    /**
     * @depends testCreateAnywhereCache
     */
    public function testGetAnywhereCache()
    {
        $output = $this->runFunctionSnippet('get_anywhere_cache', [
            self::$sourceBucket->name(),
            self::$cacheId,
        ]);

        $this->assertStringContainsString(
            sprintf('Got anywhere cache: %s', self::$anywhereCacheName),
            $output
        );
    }

    /**
     * @depends testGetAnywhereCache
     */
    public function testListAnywhereCaches()
    {
        $output = $this->runFunctionSnippet('list_anywhere_caches', [
            self::$sourceBucket->name(),
        ]);

        $this->assertStringContainsString(
            sprintf('Anywhere cache name: %s', self::$anywhereCacheName),
            $output
        );
    }

    /**
     * @depends testListAnywhereCaches
     */
    public function testPauseAnywhereCache()
    {
        $output = $this->runFunctionSnippet('pause_anywhere_cache', [
            self::$sourceBucket->name(),
            self::$cacheId,
        ]);

        $this->assertStringContainsString(
            sprintf('Paused anywhere cache: %s', self::$anywhereCacheName),
            $output
        );
    }

    /**
     * @depends testPauseAnywhereCache
     */
    public function testResumeAnywhereCache()
    {
        $output = $this->runFunctionSnippet('resume_anywhere_cache', [
            self::$sourceBucket->name(),
            self::$cacheId,
        ]);

        $this->assertStringContainsString(
            sprintf('Resumed anywhere cache: %s', self::$anywhereCacheName),
            $output
        );
    }

    /**
     * @depends testResumeAnywhereCache
     */
    public function testUpdateAnywhereCache()
    {
        $admission_policy = 'admit-on-second-miss';
        $output = $this->runFunctionSnippet('update_anywhere_cache', [
            self::$sourceBucket->name(),
            self::$cacheId,
            $admission_policy
        ]);

        $this->assertStringContainsString(
            sprintf('Updated anywhere cache: %s', self::$anywhereCacheName),
            $output
        );
    }

    /**
     * @depends testUpdateAnywhereCache
     */
    public function testDisableAnywhereCache()
    {
        $output = $this->runFunctionSnippet('disable_anywhere_cache', [
            self::$sourceBucket->name(),
            self::$cacheId,
        ]);

        $this->assertStringContainsString(
            sprintf('Disabled anywhere cache: %s', self::$anywhereCacheName),
            $output
        );
    }
}
