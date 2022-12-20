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

    private static $inputBucketName = 'cloud-samples-data';
    private static $inputVideoFileName = '/media/hls-vod/manifest.m3u8';
    private static $vodUri;
    private static $vodAgTagUri = 'https://pubads.g.doubleclick.net/gampad/ads?iu=/21775744923/external/vmap_ad_samples&sz=640x480&cust_params=sample_ar%3Dpreonly&ciu_szs=300x250%2C728x90&gdfp_req=1&ad_rule=1&output=vmap&unviewed_position_start=1&env=vp&impl=s&correlator=';

    private static $vodSessionId;
    private static $vodSessionName;
    private static $vodAdTagDetailId;
    private static $vodAdTagDetailName;
    private static $vodStitchDetailId;
    private static $vodStitchDetailName;

    public static function setUpBeforeClass(): void
    {
        self::checkProjectEnvVars();
        self::$projectId = self::requireEnv('GOOGLE_PROJECT_ID');

        self::deleteOldSlates();
        self::deleteOldCdnKeys();

        self::$slateUri = sprintf('https://storage.googleapis.com/%s%s', self::$bucket, self::$slateFileName);
        self::$updatedSlateUri = sprintf('https://storage.googleapis.com/%s%s', self::$bucket, self::$updatedSlateFileName);

        self::$vodUri = sprintf('https://storage.googleapis.com/%s%s', self::$inputBucketName, self::$inputVideoFileName);
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

    public function testCreateVodSession()
    {
        # API returns project number rather than project ID so
        # don't include that in $vodSessionName since we don't have it
        self::$vodSessionName = sprintf('/locations/%s/vodSessions/', self::$location);

        $output = $this->runFunctionSnippet('create_vod_session', [
            self::$projectId,
            self::$location,
            self::$vodUri,
            self::$vodAgTagUri
        ]);
        $this->assertStringContainsString(self::$vodSessionName, $output);
        self::$vodSessionId = explode('/', $output);
        self::$vodSessionId = trim(self::$vodSessionId[(count(self::$vodSessionId) - 1)]);
        self::$vodSessionName = sprintf('/locations/%s/vodSessions/%s', self::$location, self::$vodSessionId);
    }

    /** @depends testCreateVodSession */
    public function testGetVodSession()
    {
        $output = $this->runFunctionSnippet('get_vod_session', [
            self::$projectId,
            self::$location,
            self::$vodSessionId
        ]);
        $this->assertStringContainsString(self::$vodSessionName, $output);
    }

    /** @depends testGetVodSession */
    public function testListVodAdTagDetails()
    {
        self::$vodAdTagDetailName = sprintf('/locations/%s/vodSessions/%s/vodAdTagDetails/', self::$location, self::$vodSessionId);
        $output = $this->runFunctionSnippet('list_vod_ad_tag_details', [
            self::$projectId,
            self::$location,
            self::$vodSessionId
        ]);
        $this->assertStringContainsString(self::$vodAdTagDetailName, $output);
        self::$vodAdTagDetailId = explode('/', $output);
        self::$vodAdTagDetailId = trim(self::$vodAdTagDetailId[(count(self::$vodAdTagDetailId) - 1)]);
        self::$vodAdTagDetailName = sprintf('/locations/%s/vodSessions/%s/vodAdTagDetails/%s', self::$location, self::$vodSessionId, self::$vodAdTagDetailId);
    }

    /** @depends testListVodAdTagDetails */
    public function testGetVodAdTagDetail()
    {
        $output = $this->runFunctionSnippet('get_vod_ad_tag_detail', [
            self::$projectId,
            self::$location,
            self::$vodSessionId,
            self::$vodAdTagDetailId
        ]);
        $this->assertStringContainsString(self::$vodAdTagDetailName, $output);
    }

    /** @depends testCreateVodSession */
    public function testListVodStitchDetails()
    {
        self::$vodStitchDetailName = sprintf('/locations/%s/vodSessions/%s/vodStitchDetails/', self::$location, self::$vodSessionId);
        $output = $this->runFunctionSnippet('list_vod_stitch_details', [
            self::$projectId,
            self::$location,
            self::$vodSessionId
        ]);
        $this->assertStringContainsString(self::$vodStitchDetailName, $output);
        self::$vodStitchDetailId = explode('/', $output);
        self::$vodStitchDetailId = trim(self::$vodStitchDetailId[(count(self::$vodStitchDetailId) - 1)]);
        self::$vodStitchDetailName = sprintf('/locations/%s/vodSessions/%s/vodStitchDetails/%s', self::$location, self::$vodSessionId, self::$vodStitchDetailId);
    }

    /** @depends testListVodStitchDetails */
    public function testGetVodStitchDetail()
    {
        $output = $this->runFunctionSnippet('get_vod_stitch_detail', [
            self::$projectId,
            self::$location,
            self::$vodSessionId,
            self::$vodStitchDetailId
        ]);
        $this->assertStringContainsString(self::$vodStitchDetailName, $output);
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
