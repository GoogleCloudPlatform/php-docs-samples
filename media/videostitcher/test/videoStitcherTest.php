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
use Google\Cloud\Video\Stitcher\V1\Client\VideoStitcherServiceClient;
use Google\Cloud\Video\Stitcher\V1\DeleteCdnKeyRequest;
use Google\Cloud\Video\Stitcher\V1\DeleteLiveConfigRequest;
use Google\Cloud\Video\Stitcher\V1\DeleteVodConfigRequest;
use Google\Cloud\Video\Stitcher\V1\DeleteSlateRequest;
use Google\Cloud\Video\Stitcher\V1\GetLiveSessionRequest;
use Google\Cloud\Video\Stitcher\V1\ListCdnKeysRequest;
use Google\Cloud\Video\Stitcher\V1\ListLiveConfigsRequest;
use Google\Cloud\Video\Stitcher\V1\ListVodConfigsRequest;
use Google\Cloud\Video\Stitcher\V1\ListSlatesRequest;
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

    private static $liveConfigId;
    private static $liveConfigName;

    private static $inputBucketName = 'cloud-samples-data';
    private static $inputVodFileName = '/media/hls-vod/manifest.m3u8';
    private static $updatedInputVodFileName = '/media/hls-vod/manifest.mpd';

    private static $vodUri;
    private static $updatedVodUri;

    private static $vodAgTagUri = 'https://pubads.g.doubleclick.net/gampad/ads?iu=/21775744923/external/vmap_ad_samples&sz=640x480&cust_params=sample_ar%3Dpreonly&ciu_szs=300x250%2C728x90&gdfp_req=1&ad_rule=1&output=vmap&unviewed_position_start=1&env=vp&impl=s&correlator=';

    private static $vodConfigId;
    private static $vodConfigName;

    private static $vodSessionId;
    private static $vodSessionName;
    private static $vodAdTagDetailId;
    private static $vodAdTagDetailName;
    private static $vodStitchDetailId;
    private static $vodStitchDetailName;

    private static $inputLiveFileName = '/media/hls-live/manifest.m3u8';
    private static $liveUri;
    private static $liveAgTagUri = 'https://pubads.g.doubleclick.net/gampad/ads?iu=/21775744923/external/single_ad_samples&sz=640x480&cust_params=sample_ct%3Dlinear&ciu_szs=300x250%2C728x90&gdfp_req=1&output=vast&unviewed_position_start=1&env=vp&impl=s&correlator=';
    private static $liveSessionId;
    private static $liveSessionName;
    private static $liveAdTagDetailId;
    private static $liveAdTagDetailName;

    public static function setUpBeforeClass(): void
    {
        self::checkProjectEnvVars();
        self::$projectId = self::requireEnv('GOOGLE_PROJECT_ID');

        self::deleteOldSlates();
        self::deleteOldCdnKeys();
        self::deleteOldLiveConfigs();
        self::deleteOldVodConfigs();

        self::$slateUri = sprintf('https://storage.googleapis.com/%s%s', self::$bucket, self::$slateFileName);
        self::$updatedSlateUri = sprintf('https://storage.googleapis.com/%s%s', self::$bucket, self::$updatedSlateFileName);

        self::$vodUri = sprintf('https://storage.googleapis.com/%s%s', self::$inputBucketName, self::$inputVodFileName);
        self::$updatedVodUri = sprintf('https://storage.googleapis.com/%s%s', self::$inputBucketName, self::$updatedInputVodFileName);

        self::$liveUri = sprintf('https://storage.googleapis.com/%s%s', self::$inputBucketName, self::$inputLiveFileName);
    }

    public function testCreateSlate()
    {
        self::$slateId = sprintf('php-test-slate-%s-%s', uniqid(), time());
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
        self::$cloudCdnKeyId = sprintf('php-test-cloud-cdn-key-%s-%s', uniqid(), time());
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
        self::$mediaCdnKeyId = sprintf('php-test-media-cdn-key-%s-%s', uniqid(), time());
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
        self::$akamaiCdnKeyId = sprintf('php-test-akamai-cdn-key-%s-%s', uniqid(), time());
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

    public function testCreateLiveConfig()
    {
        # Create a temporary slate for the live session (required)
        $tempSlateId = sprintf('php-test-slate-%s-%s', uniqid(), time());
        $this->runFunctionSnippet('create_slate', [
            self::$projectId,
            self::$location,
            $tempSlateId,
            self::$slateUri
        ]);

        self::$liveConfigId = sprintf('php-test-live-config-%s-%s', uniqid(), time());
        # API returns project number rather than project ID so
        # don't include that in $liveConfigName since we don't have it
        self::$liveConfigName = sprintf('/locations/%s/liveConfigs/%s', self::$location, self::$liveConfigId);

        $output = $this->runFunctionSnippet('create_live_config', [
            self::$projectId,
            self::$location,
            self::$liveConfigId,
            self::$liveUri,
            self::$liveAgTagUri,
            $tempSlateId
        ]);
        $this->assertStringContainsString(self::$liveConfigName, $output);
    }

    /** @depends testCreateLiveConfig */
    public function testListLiveConfigs()
    {
        $output = $this->runFunctionSnippet('list_live_configs', [
            self::$projectId,
            self::$location
        ]);
        $this->assertStringContainsString(self::$liveConfigName, $output);
    }

    /** @depends testListLiveConfigs */
    public function testGetLiveConfig()
    {
        $output = $this->runFunctionSnippet('get_live_config', [
            self::$projectId,
            self::$location,
            self::$liveConfigId
        ]);
        $this->assertStringContainsString(self::$liveConfigName, $output);
    }

    /** @depends testGetLiveConfig */
    public function testDeleteLiveConfig()
    {
        $output = $this->runFunctionSnippet('delete_live_config', [
            self::$projectId,
            self::$location,
            self::$liveConfigId
        ]);
        $this->assertStringContainsString('Deleted live config', $output);
    }

    public function testCreateVodConfig()
    {
        self::$vodConfigId = sprintf('php-test-vod-config-%s-%s', uniqid(), time());
        # API returns project number rather than project ID so
        # don't include that in $vodConfigName since we don't have it
        self::$vodConfigName = sprintf('/locations/%s/vodConfigs/%s', self::$location, self::$vodConfigId);

        $output = $this->runFunctionSnippet('create_vod_config', [
            self::$projectId,
            self::$location,
            self::$vodConfigId,
            self::$vodUri,
            self::$vodAgTagUri
        ]);
        $this->assertStringContainsString(self::$vodConfigName, $output);
    }

    /** @depends testCreateVodConfig */
    public function testListVodConfigs()
    {
        $output = $this->runFunctionSnippet('list_vod_configs', [
            self::$projectId,
            self::$location
        ]);
        $this->assertStringContainsString(self::$vodConfigName, $output);
    }

    /** @depends testListVodConfigs */
    public function testUpdateVodConfig()
    {
        $output = $this->runFunctionSnippet('update_vod_config', [
            self::$projectId,
            self::$location,
            self::$vodConfigId,
            self::$updatedVodUri
        ]);
        $this->assertStringContainsString(self::$vodConfigName, $output);
    }

    /** @depends testUpdateVodConfig */
    public function testGetVodConfig()
    {
        $output = $this->runFunctionSnippet('get_vod_config', [
            self::$projectId,
            self::$location,
            self::$vodConfigId
        ]);
        $this->assertStringContainsString(self::$vodConfigName, $output);
    }

    /** @depends testGetVodConfig */
    public function testDeleteVodConfig()
    {
        $output = $this->runFunctionSnippet('delete_vod_config', [
            self::$projectId,
            self::$location,
            self::$vodConfigId
        ]);
        $this->assertStringContainsString('Deleted VOD config', $output);
    }

    public function testCreateVodSession()
    {
        # Create a temporary VOD config for the VOD session (required)
        $tempVodConfigId = sprintf('php-test-vod-config-%s-%s', uniqid(), time());
        $this->runFunctionSnippet('create_vod_config', [
            self::$projectId,
            self::$location,
            $tempVodConfigId,
            self::$vodUri,
            self::$vodAgTagUri
        ]);

        # API returns project number rather than project ID so
        # don't include that in $vodSessionName since we don't have it
        self::$vodSessionName = sprintf('/locations/%s/vodSessions/', self::$location);

        $output = $this->runFunctionSnippet('create_vod_session', [
            self::$projectId,
            self::$location,
            $tempVodConfigId
        ]);
        $this->assertStringContainsString(self::$vodSessionName, $output);
        self::$vodSessionId = explode('/', $output);
        self::$vodSessionId = trim(self::$vodSessionId[(count(self::$vodSessionId) - 1)]);
        self::$vodSessionName = sprintf('/locations/%s/vodSessions/%s', self::$location, self::$vodSessionId);

        # Delete the temporary VOD config
        $this->runFunctionSnippet('delete_vod_config', [
            self::$projectId,
            self::$location,
            $tempVodConfigId
        ]);
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

    public function testCreateLiveSession()
    {
        # Create a temporary slate for the live session (required)
        $tempSlateId = sprintf('php-test-slate-%s-%s', uniqid(), time());
        $this->runFunctionSnippet('create_slate', [
            self::$projectId,
            self::$location,
            $tempSlateId,
            self::$slateUri
        ]);

        # Create a temporary live config for the live session (required)
        $tempLiveConfigId = sprintf('php-test-live-config-%s-%s', uniqid(), time());
        $this->runFunctionSnippet('create_live_config', [
            self::$projectId,
            self::$location,
            $tempLiveConfigId,
            self::$liveUri,
            self::$liveAgTagUri,
            $tempSlateId
        ]);

        # API returns project number rather than project ID so
        # don't include that in $liveSessionName since we don't have it
        self::$liveSessionName = sprintf('/locations/%s/liveSessions/', self::$location);

        $output = $this->runFunctionSnippet('create_live_session', [
            self::$projectId,
            self::$location,
            $tempLiveConfigId
        ]);
        $this->assertStringContainsString(self::$liveSessionName, $output);
        self::$liveSessionId = explode('/', $output);
        self::$liveSessionId = trim(self::$liveSessionId[(count(self::$liveSessionId) - 1)]);
        self::$liveSessionName = sprintf('/locations/%s/liveSessions/%s', self::$location, self::$liveSessionId);

        # Delete the temporary live config
        $this->runFunctionSnippet('delete_live_config', [
            self::$projectId,
            self::$location,
            $tempLiveConfigId
        ]);

        # Delete the temporary slate
        $this->runFunctionSnippet('delete_slate', [
            self::$projectId,
            self::$location,
            $tempSlateId
        ]);
    }

    /** @depends testCreateLiveSession */
    public function testGetLiveSession()
    {
        $output = $this->runFunctionSnippet('get_live_session', [
            self::$projectId,
            self::$location,
            self::$liveSessionId
        ]);
        $this->assertStringContainsString(self::$liveSessionName, $output);
    }

    /** @depends testGetLiveSession */
    public function testListLiveAdTagDetails()
    {
        # To get ad tag details, you need to curl the main manifest and
        # a rendition first. This supplies media player information to the API.
        #
        # Curl the playUri first. The last line of the response will contain a
        # renditions location. Curl the live session name with the rendition
        # location appended.

        $stitcherClient = new VideoStitcherServiceClient();
        $formattedName = $stitcherClient->liveSessionName(self::$projectId, self::$location, self::$liveSessionId);
        $getLiveSessionRequest = (new GetLiveSessionRequest())
            ->setName($formattedName);
        $session = $stitcherClient->getLiveSession($getLiveSessionRequest);
        $playUri = $session->getPlayUri();

        $manifest = file_get_contents($playUri);
        $tmp = explode("\n", trim($manifest));
        $renditions = $tmp[count($tmp) - 1];

        # playUri will be in the following format:
        # https://videostitcher.googleapis.com/v1/projects/{project}/locations/{location}/liveSessions/{session-id}/manifest.m3u8?signature=...
        # Replace manifest.m3u8?signature=... with the renditions location.

        $tmp = explode('/', $playUri);
        array_pop($tmp);
        $renditionsUri = sprintf('%s/%s', join('/', $tmp), $renditions);
        file_get_contents($renditionsUri);

        self::$liveAdTagDetailName = sprintf('/locations/%s/liveSessions/%s/liveAdTagDetails/', self::$location, self::$liveSessionId);
        $output = $this->runFunctionSnippet('list_live_ad_tag_details', [
            self::$projectId,
            self::$location,
            self::$liveSessionId
        ]);
        $this->assertStringContainsString(self::$liveAdTagDetailName, $output);
        self::$liveAdTagDetailId = explode('/', $output);
        self::$liveAdTagDetailId = trim(self::$liveAdTagDetailId[(count(self::$liveAdTagDetailId) - 1)]);
        self::$liveAdTagDetailName = sprintf('/locations/%s/liveSessions/%s/liveAdTagDetails/%s', self::$location, self::$liveSessionId, self::$liveAdTagDetailId);
    }

    /** @depends testListLiveAdTagDetails */
    public function testGetLiveAdTagDetail()
    {
        $output = $this->runFunctionSnippet('get_live_ad_tag_detail', [
            self::$projectId,
            self::$location,
            self::$liveSessionId,
            self::$liveAdTagDetailId
        ]);
        $this->assertStringContainsString(self::$liveAdTagDetailName, $output);
    }

    private static function deleteOldSlates(): void
    {
        $stitcherClient = new VideoStitcherServiceClient();
        $parent = $stitcherClient->locationName(self::$projectId, self::$location);
        $listSlatesRequest = (new ListSlatesRequest())
            ->setParent($parent);
        $response = $stitcherClient->listSlates($listSlatesRequest);
        $slates = $response->iterateAllElements();

        $currentTime = time();
        $oneHourInSecs = 60 * 60 * 1;

        foreach ($slates as $slate) {
            if (str_contains($slate->getName(), 'php-test-')) {
                $tmp = explode('/', $slate->getName());
                $id = end($tmp);
                $tmp = explode('-', $id);
                $timestamp = intval(end($tmp));

                if ($currentTime - $timestamp >= $oneHourInSecs) {
                    $deleteSlateRequest = (new DeleteSlateRequest())
                        ->setName($slate->getName());
                    $stitcherClient->deleteSlate($deleteSlateRequest);
                }
            }
        }
    }

    private static function deleteOldCdnKeys(): void
    {
        $stitcherClient = new VideoStitcherServiceClient();
        $parent = $stitcherClient->locationName(self::$projectId, self::$location);
        $listCdnKeysRequest = (new ListCdnKeysRequest())
            ->setParent($parent);
        $response = $stitcherClient->listCdnKeys($listCdnKeysRequest);
        $keys = $response->iterateAllElements();

        $currentTime = time();
        $oneHourInSecs = 60 * 60 * 1;

        foreach ($keys as $key) {
            if (str_contains($key->getName(), 'php-test-')) {
                $tmp = explode('/', $key->getName());
                $id = end($tmp);
                $tmp = explode('-', $id);
                $timestamp = intval(end($tmp));

                if ($currentTime - $timestamp >= $oneHourInSecs) {
                    $deleteCdnKeyRequest = (new DeleteCdnKeyRequest())
                        ->setName($key->getName());
                    $stitcherClient->deleteCdnKey($deleteCdnKeyRequest);
                }
            }
        }
    }

    private static function deleteOldLiveConfigs(): void
    {
        $stitcherClient = new VideoStitcherServiceClient();
        $parent = $stitcherClient->locationName(self::$projectId, self::$location);
        $listLiveConfigsRequest = (new ListLiveConfigsRequest())
            ->setParent($parent);
        $response = $stitcherClient->listLiveConfigs($listLiveConfigsRequest);
        $liveConfigs = $response->iterateAllElements();

        $currentTime = time();
        $oneHourInSecs = 60 * 60 * 1;

        foreach ($liveConfigs as $liveConfig) {
            if (str_contains($liveConfig->getName(), 'php-test-')) {
                $tmp = explode('/', $liveConfig->getName());
                $id = end($tmp);
                $tmp = explode('-', $id);
                $timestamp = intval(end($tmp));

                if ($currentTime - $timestamp >= $oneHourInSecs) {
                    $deleteLiveConfigRequest = (new DeleteLiveConfigRequest())
                        ->setName($liveConfig->getName());
                    $stitcherClient->deleteLiveConfig($deleteLiveConfigRequest);
                }
            }
        }
    }

    private static function deleteOldVodConfigs(): void
    {
        $stitcherClient = new VideoStitcherServiceClient();
        $parent = $stitcherClient->locationName(self::$projectId, self::$location);
        $listVodConfigsRequest = (new ListVodConfigsRequest())
            ->setParent($parent);
        $response = $stitcherClient->listVodConfigs($listVodConfigsRequest);
        $vodConfigs = $response->iterateAllElements();

        $currentTime = time();
        $oneHourInSecs = 60 * 60 * 1;

        foreach ($vodConfigs as $vodConfig) {
            if (str_contains($vodConfig->getName(), 'php-test-')) {
                $tmp = explode('/', $vodConfig->getName());
                $id = end($tmp);
                $tmp = explode('-', $id);
                $timestamp = intval(end($tmp));

                if ($currentTime - $timestamp >= $oneHourInSecs) {
                    $deleteVodConfigRequest = (new DeleteVodConfigRequest())
                        ->setName($vodConfig->getName());
                    $stitcherClient->deleteVodConfig($deleteVodConfigRequest);
                }
            }
        }
    }
}
