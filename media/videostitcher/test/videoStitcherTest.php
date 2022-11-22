<?php

/**
 * Copyright 2022 Google LLC.
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
declare(strict_types=1);

namespace Google\Cloud\Samples\Media\Stitcher;

use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\Video\Stitcher\V1\VideoStitcherServiceClient;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for Video Stitcher commands.
 */
class videoStitcherTest extends TestCase
{
    use TestTrait;
    use EventuallyConsistentTestTrait;

    private static $projectId;
    private static $location = 'us-central1';

    private static $bucket = 'cloud-samples-data/media/';
    private static $slateFileName = 'ForBiggerEscapes.mp4';
    private static $updatedSlateFileName = 'ForBiggerJoyrides.mp4';

    private static $slateUri;
    private static $updatedSlateUri;

    private static $hostname = 'cdn.example.com';
    private static $updatedHostname = 'updated.example.com';

    private static $cloudCdnKeyName = 'cloud-cdn-key';
    private static $updatedCloudCdnKeyName = 'updated-cloud-cdn-key';
    private static $mediaCdnKeyName = 'media-cdn-key';
    private static $updatedMediaCdnKeyName = 'updated-media-cdn-key';

    private static $cloudCdnPrivateKey = 'VGhpcyBpcyBhIHRlc3Qgc3RyaW5nLg==';
    private static $updatedCloudCdnPrivateKey = 'VGhpcyBpcyBhbiB1cGRhdGVkIHRlc3Qgc3RyaW5nLg==';
    private static $mediaCdnPrivateKey = 'MTIzNDU2Nzg5MDEyMzQ1Njc4OTAxzg5MDEyMzQ1Njc4OTAxMjM0NTY3DkwMTIzNA';
    private static $updatedMediaCdnPrivateKey ='ZZZzNDU2Nzg5MDEyMzQ1Njc4OTAxzg5MDEyMzQ1Njc4OTAxMjM0NTY3DkwMTIZZZ';
    private static $akamaiKey = 'VGhpcyBpcyBhIHRlc3Qgc3RyaW5nLg==';
    private static $updatedAkamaiKey = 'VGhpcyBpcyBhbiB1cGRhdGVkIHRlc3Qgc3RyaW5nLg==';

    public static function setUpBeforeClass(): void
    {
        self::checkProjectEnvVars();
        self::$projectId = self::requireEnv('GOOGLE_PROJECT_ID');

        self::deleteOldSlates();
        self::deleteOldCdnKeys();

        self::$slateUri = sprintf('https://storage.googleapis.com/%s%s', self::$bucket, self::$slateFileName);
        self::$updatedSlateUri = sprintf('https://storage.googleapis.com/%s%s', self::$bucket, self::$updatedSlateFileName);
    }

    public function testSlates()
    {
        $slateId = sprintf('php-test-slate-%s', time());
        # API returns project number rather than project ID so
        # don't include that in $slateName since we don't have it
        $slateName = sprintf('/locations/%s/slates/%s', self::$location, $slateId);

        $output = $this->runFunctionSnippet('create_slate', [
            self::$projectId,
            self::$location,
            $slateId,
            self::$slateUri
        ]);
        $this->assertStringContainsString($slateName, $output);

        $output = $this->runFunctionSnippet('get_slate', [
            self::$projectId,
            self::$location,
            $slateId
        ]);
        $this->assertStringContainsString($slateName, $output);

        $output = $this->runFunctionSnippet('list_slates', [
            self::$projectId,
            self::$location
        ]);
        $this->assertStringContainsString($slateName, $output);

        $output = $this->runFunctionSnippet('update_slate', [
            self::$projectId,
            self::$location,
            $slateId,
            self::$updatedSlateUri
        ]);
        $this->assertStringContainsString($slateName, $output);

        $output = $this->runFunctionSnippet('delete_slate', [
            self::$projectId,
            self::$location,
            $slateId
        ]);
        $this->assertStringContainsString('Deleted slate', $output);
    }

    public function testCdnKeys()
    {
        $cdnKeyId = sprintf('php-test-cloud-cdn-key-%s', time());
        # API returns project number rather than project ID so
        # don't include that in $cdnKeyName since we don't have it
        $cdnKeyName = sprintf('/locations/%s/cdnKeys/%s', self::$location, $cdnKeyId);

        // Test Cloud CDN keys

        $output = $this->runFunctionSnippet('create_cdn_key', [
            self::$projectId,
            self::$location,
            $cdnKeyId,
            self::$hostname,
            self::$cloudCdnKeyName,
            self::$cloudCdnPrivateKey,
            false
        ]);
        $this->assertStringContainsString($cdnKeyName, $output);

        $output = $this->runFunctionSnippet('get_cdn_key', [
            self::$projectId,
            self::$location,
            $cdnKeyId
        ]);
        $this->assertStringContainsString($cdnKeyName, $output);

        $output = $this->runFunctionSnippet('list_cdn_keys', [
            self::$projectId,
            self::$location
        ]);
        $this->assertStringContainsString($cdnKeyName, $output);

        $output = $this->runFunctionSnippet('update_cdn_key', [
            self::$projectId,
            self::$location,
            $cdnKeyId,
            self::$updatedHostname,
            self::$updatedCloudCdnKeyName,
            self::$updatedCloudCdnPrivateKey,
            false
        ]);
        $this->assertStringContainsString($cdnKeyName, $output);

        $output = $this->runFunctionSnippet('delete_cdn_key', [
            self::$projectId,
            self::$location,
            $cdnKeyId
        ]);
        $this->assertStringContainsString('Deleted CDN key', $output);

        // Test Media CDN keys - create, update, delete only

        $cdnKeyId = sprintf('php-test-media-cdn-key-%s', time());
        # API returns project number rather than project ID so
        # don't include that in $cdnKeyName since we don't have it
        $cdnKeyName = sprintf('/locations/%s/cdnKeys/%s', self::$location, $cdnKeyId);

        $output = $this->runFunctionSnippet('create_cdn_key', [
                    self::$projectId,
                    self::$location,
                    $cdnKeyId,
                    self::$hostname,
                    self::$mediaCdnKeyName,
                    self::$mediaCdnPrivateKey,
                    true
                ]);
        $this->assertStringContainsString($cdnKeyName, $output);

        $output = $this->runFunctionSnippet('update_cdn_key', [
            self::$projectId,
            self::$location,
            $cdnKeyId,
            self::$updatedHostname,
            self::$updatedMediaCdnKeyName,
            self::$updatedMediaCdnPrivateKey,
            true
        ]);
        $this->assertStringContainsString($cdnKeyName, $output);

        $output = $this->runFunctionSnippet('delete_cdn_key', [
            self::$projectId,
            self::$location,
            $cdnKeyId
        ]);
        $this->assertStringContainsString('Deleted CDN key', $output);

        // Test Akamai CDN keys - create, update, delete only

        $cdnKeyId = sprintf('php-test-akamai-cdn-key-%s', time());
        # API returns project number rather than project ID so
        # don't include that in $cdnKeyName since we don't have it
        $cdnKeyName = sprintf('/locations/%s/cdnKeys/%s', self::$location, $cdnKeyId);

        $output = $this->runFunctionSnippet('create_cdn_key_akamai', [
                    self::$projectId,
                    self::$location,
                    $cdnKeyId,
                    self::$hostname,
                    self::$akamaiKey
                ]);
        $this->assertStringContainsString($cdnKeyName, $output);

        $output = $this->runFunctionSnippet('update_cdn_key_akamai', [
                    self::$projectId,
                    self::$location,
                    $cdnKeyId,
                    self::$updatedHostname,
                    self::$updatedAkamaiKey
        ]);
        $this->assertStringContainsString($cdnKeyName, $output);

        $output = $this->runFunctionSnippet('delete_cdn_key', [
            self::$projectId,
            self::$location,
            $cdnKeyId
        ]);
        $this->assertStringContainsString('Deleted CDN key', $output);
    }

    private static function deleteOldSlates(): void
    {
        $stitcherClient = new VideoStitcherServiceClient();
        $parent = $stitcherClient->locationName(self::$projectId, self::$location);
        $response = $stitcherClient->listSlates($parent);
        $slates = $response->iterateAllElements();

        $currentTime = time();
        $oneHourInSecs = 60 * 60 * 1;

        foreach ($slates as $slate) {
            $tmp = explode('/', $slate->getName());
            $id = end($tmp);
            $tmp = explode('-', $id);
            $timestamp = intval(end($tmp));

            if ($currentTime - $timestamp >= $oneHourInSecs) {
                $stitcherClient->deleteSlate($slate->getName());
            }
        }
    }

    private static function deleteOldCdnKeys(): void
    {
        $stitcherClient = new VideoStitcherServiceClient();
        $parent = $stitcherClient->locationName(self::$projectId, self::$location);
        $response = $stitcherClient->listCdnKeys($parent);
        $keys = $response->iterateAllElements();

        $currentTime = time();
        $oneHourInSecs = 60 * 60 * 1;

        foreach ($keys as $key) {
            $tmp = explode('/', $key->getName());
            $id = end($tmp);
            $tmp = explode('-', $id);
            $timestamp = intval(end($tmp));

            if ($currentTime - $timestamp >= $oneHourInSecs) {
                $stitcherClient->deleteCdnKey($key->getName());
            }
        }
    }
}
