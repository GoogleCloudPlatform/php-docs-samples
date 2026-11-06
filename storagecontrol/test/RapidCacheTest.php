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

use Google\Cloud\Core\Exception\ConflictException;
use Google\Cloud\Storage\Control\V2\Client\StorageControlClient;
use Google\Cloud\Storage\Control\V2\DisableRapidCacheRequest;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Rapid Cache storage control samples.
 */
class RapidCacheTest extends TestCase
{
    use TestTrait;

    // Wait for bucket metadata to propagate before creating the Rapid Cache.
    private const BUCKET_PROPAGATION_SECONDS = 20;

    // Maximum time to wait for the disable operation (10 minutes). The cache
    // moves to the "disabled" state within seconds, but the operation itself
    // can run for more than an hour.
    private const DISABLE_TIMEOUT_MILLIS = 600000;

    // New time to live (TTL) set by the update step: 172800 seconds (2 days).
    private const UPDATED_TTL_SECONDS = 172800;

    // Best-effort bucket cleanup: retry for up to ~2 minutes.
    private const DELETE_RETRY_ATTEMPTS = 6;
    private const DELETE_RETRY_INTERVAL_SECONDS = 20;

    private static $sourceBucket;
    private static $storage;
    private static $location;
    private static $zone;
    private static $cacheName;
    private static $cacheActive = false;

    public static function setUpBeforeClass(): void
    {
        self::checkProjectEnvVars();
        self::$storage = new StorageClient();
        self::$zone = getenv('RAPID_CACHE_ZONE') ?: 'us-central1-a';
        // The bucket must be in the same region as the Rapid Cache zone.
        self::$location = substr(self::$zone, 0, strrpos(self::$zone, '-'));
        $uniqueBucketId = time() . rand();
        // Rapid Cache Ultra requires a regional bucket with hierarchical
        // namespace enabled.
        self::$sourceBucket = self::$storage->createBucket(
            sprintf('php-gcscontrol-rc-%s', $uniqueBucketId),
            [
                'location' => self::$location,
                'hierarchicalNamespace' => ['enabled' => true],
                'iamConfiguration' => ['uniformBucketLevelAccess' => ['enabled' => true]]
            ]
        );
        sleep(self::BUCKET_PROPAGATION_SECONDS);
    }

    public static function tearDownAfterClass(): void
    {
        if (self::$cacheActive) {
            self::disableCacheQuietly();
        }
        if (self::$sourceBucket) {
            self::deleteBucketQuietly();
        }
    }

    /**
     * Starts disabling the Rapid Cache if the test did not, so that the bucket
     * can be deleted once the cache has been removed. Errors are ignored.
     */
    private static function disableCacheQuietly(): void
    {
        try {
            $storageControlClient = new StorageControlClient();
            $storageControlClient->disableRapidCache(new DisableRapidCacheRequest([
                'name' => self::$cacheName,
            ]));
        } catch (\Exception $e) {
            fwrite(STDERR, sprintf(
                'Could not disable Rapid Cache %s: %s' . PHP_EOL,
                self::$cacheName,
                $e->getMessage()
            ));
        }
    }

    /**
     * Deletes the test bucket on a best-effort basis. A bucket cannot be
     * deleted until its disabled Rapid Cache has been fully removed, which can
     * take more than two hours, so this retries briefly and then leaves the
     * bucket behind for later cleanup.
     */
    private static function deleteBucketQuietly(): void
    {
        $lastError = 'unknown error';
        for ($attempt = 1; $attempt <= self::DELETE_RETRY_ATTEMPTS; $attempt++) {
            if ($attempt > 1) {
                sleep(self::DELETE_RETRY_INTERVAL_SECONDS);
            }
            try {
                self::$sourceBucket->delete();
                return;
            } catch (ConflictException $e) {
                $lastError = $e->getMessage();
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                break;
            }
        }
        fwrite(STDERR, sprintf(
            'Could not delete bucket %s: %s' . PHP_EOL,
            self::$sourceBucket->name(),
            $lastError
        ));
    }

    public function testRapidCacheLifecycle()
    {
        $bucketName = self::$sourceBucket->name();
        // The Rapid Cache ID must be the same as the zone.
        $cacheId = self::$zone;
        $cacheName = StorageControlClient::rapidCacheName('_', $bucketName, $cacheId);
        self::$cacheName = $cacheName;

        // Test create Rapid Cache. Mark the cache as active before creating it
        // so that tearDownAfterClass disables it even if this step fails after
        // the cache was created.
        self::$cacheActive = true;
        $output = $this->runFunctionSnippet('create_rapid_cache', [
            $bucketName,
            self::$zone,
        ]);
        $this->assertStringContainsString(sprintf('Created Rapid Cache: %s', $cacheName), $output);

        // Test get Rapid Cache
        $output = $this->runFunctionSnippet('get_rapid_cache', [
            $bucketName,
            $cacheId,
        ]);
        $this->assertStringContainsString(sprintf('Got Rapid Cache: %s', $cacheName), $output);
        $this->assertStringContainsString(sprintf('Zone: %s', self::$zone), $output);
        $this->assertStringContainsString('Cache type: rapid-cache-ultra', $output);
        $this->assertStringContainsString('State: running', $output);

        // Test list Rapid Caches
        $output = $this->runFunctionSnippet('list_rapid_caches', [
            $bucketName,
        ]);
        $this->assertStringContainsString(sprintf('Rapid Cache name: %s', $cacheName), $output);

        // Test update Rapid Cache
        $output = $this->runFunctionSnippet('update_rapid_cache', [
            $bucketName,
            $cacheId,
            self::UPDATED_TTL_SECONDS,
        ]);
        $this->assertStringContainsString(sprintf('Updated Rapid Cache: %s', $cacheName), $output);
        $this->assertStringContainsString(sprintf('TTL: %d seconds', self::UPDATED_TTL_SECONDS), $output);

        // Test disable Rapid Cache, with a bounded wait (the native
        // totalPollTimeoutMillis LRO option) so that the test cannot hang on
        // the long-running operation.
        $output = $this->runFunctionSnippet('disable_rapid_cache', [
            $bucketName,
            $cacheId,
            self::DISABLE_TIMEOUT_MILLIS,
        ]);
        self::$cacheActive = false;
        if (str_contains($output, 'Disable operation is still running')) {
            // The operation did not finish in time; the cache state must
            // already show "disabled".
            $output = $this->runFunctionSnippet('get_rapid_cache', [
                $bucketName,
                $cacheId,
            ]);
            $this->assertStringContainsString('State: disabled', $output);
        } else {
            $this->assertStringContainsString(sprintf('Disabled Rapid Cache: %s', $cacheName), $output);
        }
    }
}
