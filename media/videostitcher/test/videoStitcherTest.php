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

    private static $slateId;
    private static $slateName;

    private static $cloudCdnKeyId;
    private static $cloudCdnKeyName;
    private static $mediaCdnKeyId;
    private static $mediaCdnKeyName;
    private static $akamaiCdnKeyId;
    private static $akamaiCdnKeyName;

    private static $hostname = 'cdn.example.com';
    private static $updatedHostname = 'updated.example.com';

    private static $cloudCdnPublicKeyName = 'cloud-cdn-key';
    private static $updatedCloudCdnPublicKeyName = 'updated-cloud-cdn-key';
    private static $mediaCdnPublicKeyName = 'media-cdn-key';
    private static $updatedMediaCdnPublicKeyName = 'updated-media-cdn-key';

    private static $cloudCdnPrivateKey = 'VGhpcyBpcyBhIHRlc3Qgc3RyaW5nLg==';
    private static $updatedCloudCdnPrivateKey = 'VGhpcyBpcyBhbiB1cGRhdGVkIHRlc3Qgc3RyaW5nLg==';
    private static $mediaCdnPrivateKey = 'MTIzNDU2Nzg5MDEyMzQ1Njc4OTAxzg5MDEyMzQ1Njc4OTAxMjM0NTY3DkwMTIzNA';
    private static $updatedMediaCdnPrivateKey = 'ZZZzNDU2Nzg5MDEyMzQ1Njc4OTAxzg5MDEyMzQ1Njc4OTAxMjM0NTY3DkwMTIZZZ';
    private static $akamaiTokenKey = 'VGhpcyBpcyBhIHRlc3Qgc3RyaW5nLg==';
    private static $updatedAkamaiTokenKey = 'VGhpcyBpcyBhbiB1cGRhdGVkIHRlc3Qgc3RyaW5nLg==';

    public static function setUpBeforeClass(): void
    {
        self::checkProjectEnvVars();
        self::$projectId = self::requireEnv('GOOGLE_PROJECT_ID');

        self::deleteOldSlates();
        self::deleteOldCdnKeys();

        self::$slateUri = sprintf('https://storage.googleapis.com/%s%s', self::$bucket, self::$slateFileName);
        self::$updatedSlateUri = sprintf('https://storage.googleapis.com/%s%s', self::$bucket, self::$updatedSlateFileName);
    }

    public function testCreateSlate()
    {
        self::$slateId = sprintf('php-test-slate-%s', time());
        # API returns project number rather than project ID so
        # don't include that in $slateName since we don't have it
        self::$slateName = sprintf('/locations/%s/slates/%s', self::$location, self::$slateId);

        $output = $this->runFunctionSnippet('create_slate', [
            self::$projectId,
            self::$location,
            self::$slateId,
            self::$slateUri
        ]);
        $this->assertStringContainsString(self::$slateName, $output);
    }

    /** @depends testCreateSlate */
    public function testListSlates()
    {
        $output = $this->runFunctionSnippet('list_slates', [
            self::$projectId,
            self::$location
        ]);
        $this->assertStringContainsString(self::$slateName, $output);
    }

    /** @depends testListSlates */
    public function testUpdateSlate()
    {
        $output = $this->runFunctionSnippet('update_slate', [
            self::$projectId,
            self::$location,
            self::$slateId,
            self::$updatedSlateUri
        ]);
        $this->assertStringContainsString(self::$slateName, $output);
    }

    /** @depends testUpdateSlate */
    public function testGetSlate()
    {
        $output = $this->runFunctionSnippet('get_slate', [
            self::$projectId,
            self::$location,
            self::$slateId
        ]);
        $this->assertStringContainsString(self::$slateName, $output);
    }

    /** @depends testGetSlate */
    public function testDeleteSlate()
    {
        $output = $this->runFunctionSnippet('delete_slate', [
            self::$projectId,
            self::$location,
            self::$slateId
        ]);
        $this->assertStringContainsString('Deleted slate', $output);
    }

    public function testCreateCloudCdnKey()
    {
        self::$cloudCdnKeyId = sprintf('php-test-cloud-cdn-key-%s', time());
        # API returns project number rather than project ID so
        # don't include that in $cloudCdnKeyName since we don't have it
        self::$cloudCdnKeyName = sprintf('/locations/%s/cdnKeys/%s', self::$location, self::$cloudCdnKeyId);

        $output = $this->runFunctionSnippet('create_cdn_key', [
            self::$projectId,
            self::$location,
            self::$cloudCdnKeyId,
            self::$hostname,
            self::$cloudCdnPublicKeyName,
            self::$cloudCdnPrivateKey,
            false
        ]);
        $this->assertStringContainsString(self::$cloudCdnKeyName, $output);
    }

    /** @depends testCreateCloudCdnKey */
    public function testListCloudCdnKeys()
    {
        $output = $this->runFunctionSnippet('list_cdn_keys', [
            self::$projectId,
            self::$location
        ]);
        $this->assertStringContainsString(self::$cloudCdnKeyName, $output);
    }

    /** @depends testListCloudCdnKeys */
    public function testUpdateCloudCdnKey()
    {
        $output = $this->runFunctionSnippet('update_cdn_key', [
            self::$projectId,
            self::$location,
            self::$cloudCdnKeyId,
            self::$updatedHostname,
            self::$updatedCloudCdnPublicKeyName,
            self::$updatedCloudCdnPrivateKey,
            false
        ]);
        $this->assertStringContainsString(self::$cloudCdnKeyName, $output);
    }

    /** @depends testUpdateCloudCdnKey */
    public function testGetCloudCdnKey()
    {
        $output = $this->runFunctionSnippet('get_cdn_key', [
            self::$projectId,
            self::$location,
            self::$cloudCdnKeyId
        ]);
        $this->assertStringContainsString(self::$cloudCdnKeyName, $output);
    }

    /** @depends testGetCloudCdnKey */
    public function testDeleteCloudCdnKey()
    {
        $output = $this->runFunctionSnippet('delete_cdn_key', [
            self::$projectId,
            self::$location,
            self::$cloudCdnKeyId
        ]);
        $this->assertStringContainsString('Deleted CDN key', $output);
    }

    public function testCreateMediaCdnKey()
    {
        self::$mediaCdnKeyId = sprintf('php-test-media-cdn-key-%s', time());
        # API returns project number rather than project ID so
        # don't include that in $mediaCdnKeyName since we don't have it
        self::$mediaCdnKeyName = sprintf('/locations/%s/cdnKeys/%s', self::$location, self::$mediaCdnKeyId);

        $output = $this->runFunctionSnippet('create_cdn_key', [
            self::$projectId,
            self::$location,
            self::$mediaCdnKeyId,
            self::$hostname,
            self::$mediaCdnPublicKeyName,
            self::$mediaCdnPrivateKey,
            true
        ]);
        $this->assertStringContainsString(self::$mediaCdnKeyName, $output);
    }

    /** @depends testCreateMediaCdnKey */
    public function testListMediaCdnKeys()
    {
        $output = $this->runFunctionSnippet('list_cdn_keys', [
            self::$projectId,
            self::$location
        ]);
        $this->assertStringContainsString(self::$mediaCdnKeyName, $output);
    }

    /** @depends testListMediaCdnKeys */
    public function testUpdateMediaCdnKey()
    {
        $output = $this->runFunctionSnippet('update_cdn_key', [
            self::$projectId,
            self::$location,
            self::$mediaCdnKeyId,
            self::$updatedHostname,
            self::$updatedMediaCdnPublicKeyName,
            self::$updatedMediaCdnPrivateKey,
            true
        ]);
        $this->assertStringContainsString(self::$mediaCdnKeyName, $output);
    }

    /** @depends testUpdateMediaCdnKey */
    public function testGetMediaCdnKey()
    {
        $output = $this->runFunctionSnippet('get_cdn_key', [
            self::$projectId,
            self::$location,
            self::$mediaCdnKeyId
        ]);
        $this->assertStringContainsString(self::$mediaCdnKeyName, $output);
    }

    /** @depends testGetMediaCdnKey */
    public function testDeleteMediaCdnKey()
    {
        $output = $this->runFunctionSnippet('delete_cdn_key', [
            self::$projectId,
            self::$location,
            self::$mediaCdnKeyId
        ]);
        $this->assertStringContainsString('Deleted CDN key', $output);
    }

    public function testCreateAkamaiCdnKey()
    {
        self::$akamaiCdnKeyId = sprintf('php-test-akamai-cdn-key-%s', time());
        # API returns project number rather than project ID so
        # don't include that in $akamaiCdnKeyName since we don't have it
        self::$akamaiCdnKeyName = sprintf('/locations/%s/cdnKeys/%s', self::$location, self::$akamaiCdnKeyId);

        $output = $this->runFunctionSnippet('create_cdn_key_akamai', [
            self::$projectId,
            self::$location,
            self::$akamaiCdnKeyId,
            self::$hostname,
            self::$akamaiTokenKey
        ]);
        $this->assertStringContainsString(self::$akamaiCdnKeyName, $output);
    }

    /** @depends testCreateAkamaiCdnKey */
    public function testListAkamaiCdnKeys()
    {
        $output = $this->runFunctionSnippet('list_cdn_keys', [
            self::$projectId,
            self::$location
        ]);
        $this->assertStringContainsString(self::$akamaiCdnKeyName, $output);
    }

    /** @depends testListAkamaiCdnKeys */
    public function testUpdateAkamaiCdnKey()
    {
        $output = $this->runFunctionSnippet('update_cdn_key_akamai', [
            self::$projectId,
            self::$location,
            self::$akamaiCdnKeyId,
            self::$updatedHostname,
            self::$updatedAkamaiTokenKey
        ]);
        $this->assertStringContainsString(self::$akamaiCdnKeyName, $output);
    }

    /** @depends testUpdateAkamaiCdnKey */
    public function testGetAkamaiCdnKey()
    {
        $output = $this->runFunctionSnippet('get_cdn_key', [
            self::$projectId,
            self::$location,
            self::$akamaiCdnKeyId
        ]);
        $this->assertStringContainsString(self::$akamaiCdnKeyName, $output);
    }

    /** @depends testGetAkamaiCdnKey */
    public function testDeleteAkamaiCdnKey()
    {
        $output = $this->runFunctionSnippet('delete_cdn_key', [
            self::$projectId,
            self::$location,
            self::$akamaiCdnKeyId
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
