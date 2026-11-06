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

    // Best-effort bucket cleanup: retry for up to ~2 minutes.
    private const DELETE_RETRY_ATTEMPTS = 6;
    private const DELETE_RETRY_INTERVAL_SECONDS = 20;

    private static $sourceBucket;
    private static $storage;
    private static $location;
    private static $zone;
    private static $cacheName;
    private static $cacheDisabled = false;

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
        if (!self::$sourceBucket) {
            return;
        }
        // Disable the Rapid Cache if the test did not, so that the bucket can
        // be deleted once the cache has been removed. Errors are ignored.
        if (self::$cacheName && !self::$cacheDisabled) {
            try {
                $storageControlClient = new StorageControlClient();
                $storageControlClient->disableRapidCache(new DisableRapidCacheRequest([
                    'name' => self::$cacheName,
                ]));
            } catch (\Exception $e) {
                // Ignore errors: the cache may already be disabled.
            }
        }
        // A bucket cannot be deleted until its disabled Rapid Cache has been
        // fully removed, which can take more than two hours. Cleanup is
        // best-effort: a short retry, after which the bucket is left behind
        // for later cleanup.
        for ($attempt = 1; $attempt <= self::DELETE_RETRY_ATTEMPTS; $attempt++) {
            try {
                self::$sourceBucket->delete();
                return;
            } catch (ConflictException $e) {
                if ($attempt < self::DELETE_RETRY_ATTEMPTS) {
                    sleep(self::DELETE_RETRY_INTERVAL_SECONDS);
                }
            } catch (\Exception $e) {
                break;
            }
        }
        fwrite(STDERR, sprintf(
            'Could not delete bucket %s: %s' . PHP_EOL,
            self::$sourceBucket->name(),
            isset($e) ? $e->getMessage() : 'unknown error'
        ));
    }

    public function testRapidCacheLifecycle()
    {
        $bucketName = self::$sourceBucket->name();
        // The Rapid Cache ID must be the same as the zone.
        $cacheId = self::$zone;
        $cacheName = StorageControlClient::rapidCacheName('_', $bucketName, $cacheId);
        self::$cacheName = $cacheName;

        // Test create Rapid Cache
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
            172800,
        ]);
        $this->assertStringContainsString(sprintf('Updated Rapid Cache: %s', $cacheName), $output);
        $this->assertStringContainsString('TTL: 172800 seconds', $output);

        // Test disable Rapid Cache, with a bounded wait so that the test
        // cannot hang on the long-running operation.
        $output = $this->runFunctionSnippet('disable_rapid_cache', [
            $bucketName,
            $cacheId,
            self::DISABLE_TIMEOUT_MILLIS,
        ]);
        self::$cacheDisabled = true;
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
