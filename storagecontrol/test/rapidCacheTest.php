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
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Rapid Cache storage control samples.
 */
class rapidCacheTest extends TestCase
{
    use TestTrait;

    // Poll for up to 2 minutes for the cache to reach the "disabled" state.
    private const DISABLE_POLL_ATTEMPTS = 12;
    private const DISABLE_POLL_INTERVAL_SECONDS = 10;

    // Best-effort bucket cleanup: retry for up to ~2 minutes.
    private const DELETE_RETRY_ATTEMPTS = 6;
    private const DELETE_RETRY_INTERVAL_SECONDS = 20;

    private static $sourceBucket;
    private static $storage;
    private static $location;
    private static $zone;

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
    }

    public static function tearDownAfterClass(): void
    {
        if (!self::$sourceBucket) {
            return;
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

        // Test create Rapid Cache
        $output = $this->runFunctionSnippet('create_rapid_cache', [
            $bucketName,
            self::$zone,
        ]);
        $this->assertMatchesRegularExpression('/Created Rapid Cache: (\S+)/', $output);
        preg_match('/Created Rapid Cache: (\S+)/', $output, $matches);
        $cacheName = $matches[1];
        // The Rapid Cache ID is assigned by the server; parse it from the name.
        $cacheId = StorageControlClient::parseName($cacheName, 'rapidCache')['rapid_cache'];
        $this->assertNotEmpty($cacheId);

        try {
            // Test get Rapid Cache
            $output = $this->runFunctionSnippet('get_rapid_cache', [
                $bucketName,
                $cacheId,
            ]);
            $this->assertStringContainsString(sprintf('Got Rapid Cache: %s', $cacheName), $output);
            $this->assertStringContainsString(sprintf('Zone: %s', self::$zone), $output);

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
        } finally {
            // Disable the Rapid Cache. This also cleans up the cache so that
            // the bucket can be deleted once the cache has been fully removed.
            $disableOutput = $this->runFunctionSnippet('disable_rapid_cache', [
                $bucketName,
                $cacheId,
            ]);
        }

        // Test disable Rapid Cache
        $this->assertStringContainsString(sprintf('Disabling Rapid Cache: %s', $cacheName), $disableOutput);
        $this->assertStringContainsString('Operation: ', $disableOutput);

        // The cache moves to the "disabled" state shortly after the request.
        for ($attempt = 0; $attempt < self::DISABLE_POLL_ATTEMPTS; $attempt++) {
            $output = $this->runFunctionSnippet('get_rapid_cache', [
                $bucketName,
                $cacheId,
            ]);
            if (str_contains($output, 'State: disabled')) {
                break;
            }
            if ($attempt < self::DISABLE_POLL_ATTEMPTS - 1) {
                sleep(self::DISABLE_POLL_INTERVAL_SECONDS);
            }
        }
        $this->assertStringContainsString('State: disabled', $output);
    }
}
