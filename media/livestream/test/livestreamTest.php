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

namespace Google\Cloud\Samples\Media\LiveStream;

use Google\ApiCore\ApiException;
use Google\Cloud\TestUtils\EventuallyConsistentTestTrait;
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\Video\LiveStream\V1\LivestreamServiceClient;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for Live Stream API commands.
 */
class livestreamTest extends TestCase
{
    use TestTrait;
    use EventuallyConsistentTestTrait;

    private static $projectId;
    private static $location = 'us-central1';
    private static $inputIdPrefix = 'php-test-input';
    private static $inputId;
    private static $inputName;
    private static $backupInputId;
    private static $backupInputName;

    private static $channelIdPrefix = 'php-test-channel';
    private static $channelId;
    private static $channelName;
    private static $outputUri = 'gs://my-bucket/my-output-folder/';

    private static $eventIdPrefix = 'php-test-event';
    private static $eventId;
    private static $eventName;

    private static $assetIdPrefix = 'php-test-asset';
    private static $assetId;
    private static $assetName;
    private static $assetUri = 'gs://cloud-samples-data/media/ForBiggerEscapes.mp4';

    private static $poolId;
    private static $poolName;

    public static function setUpBeforeClass(): void
    {
        self::checkProjectEnvVars();
        self::$projectId = self::requireEnv('GOOGLE_PROJECT_ID');

        self::deleteOldChannels();
        self::deleteOldInputs();
        self::deleteOldAssets();
    }

    public function testCreateInput()
    {
        self::$inputId = sprintf('%s-%s-%s', self::$inputIdPrefix, uniqid(), time());
        self::$inputName = sprintf('projects/%s/locations/%s/inputs/%s', self::$projectId, self::$location, self::$inputId);

        $output = $this->runFunctionSnippet('create_input', [
            self::$projectId,
            self::$location,
            self::$inputId
        ]);
        $this->assertStringContainsString(self::$inputName, $output);
    }

    /** @depends testCreateInput */
    public function testListInputs()
    {
        $output = $this->runFunctionSnippet('list_inputs', [
            self::$projectId,
            self::$location
        ]);
        $this->assertStringContainsString(self::$inputName, $output);
    }

    /** @depends testListInputs */
    public function testUpdateInput()
    {
        $output = $this->runFunctionSnippet('update_input', [
            self::$projectId,
            self::$location,
            self::$inputId
        ]);
        $this->assertStringContainsString(self::$inputName, $output);

        $livestreamClient = new LivestreamServiceClient();
        $formattedName = $livestreamClient->inputName(
            self::$projectId,
            self::$location,
            self::$inputId
        );
        $input = $livestreamClient->getInput($formattedName);
        $this->assertTrue($input->getPreprocessingConfig()->hasCrop());
    }

    /** @depends testUpdateInput */
    public function testGetInput()
    {
        $output = $this->runFunctionSnippet('get_input', [
            self::$projectId,
            self::$location,
            self::$inputId
        ]);
        $this->assertStringContainsString(self::$inputName, $output);
    }

    /** @depends testGetInput */
    public function testDeleteInput()
    {
        $output = $this->runFunctionSnippet('delete_input', [
            self::$projectId,
            self::$location,
            self::$inputId
        ]);
        $this->assertStringContainsString('Deleted input', $output);
    }

    /** @depends testDeleteInput */
    public function testCreateChannel()
    {
        // Create a test input for the channel
        self::$inputId = sprintf('%s-%s-%s', self::$inputIdPrefix, uniqid(), time());
        self::$inputName = sprintf('projects/%s/locations/%s/inputs/%s', self::$projectId, self::$location, self::$inputId);

        $this->runFunctionSnippet('create_input', [
            self::$projectId,
            self::$location,
            self::$inputId
        ]);

        self::$channelId = sprintf('%s-%s-%s', self::$channelIdPrefix, uniqid(), time());
        self::$channelName = sprintf('projects/%s/locations/%s/channels/%s', self::$projectId, self::$location, self::$channelId);

        $output = $this->runFunctionSnippet('create_channel', [
            self::$projectId,
            self::$location,
            self::$channelId,
            self::$inputId,
            self::$outputUri
        ]);
        $this->assertStringContainsString(self::$channelName, $output);
    }

    /** @depends testCreateChannel */
    public function testListChannels()
    {
        $output = $this->runFunctionSnippet('list_channels', [
            self::$projectId,
            self::$location
        ]);
        $this->assertStringContainsString(self::$channelName, $output);
    }

    /** @depends testListChannels */
    public function testUpdateChannel()
    {
        // Create a test input to update the channel
        self::$backupInputId = sprintf('%s-%s-%s', self::$inputIdPrefix, uniqid(), time());
        self::$backupInputName = sprintf('projects/%s/locations/%s/inputs/%s', self::$projectId, self::$location, self::$backupInputId);

        $this->runFunctionSnippet('create_input', [
            self::$projectId,
            self::$location,
            self::$backupInputId
        ]);

        // Update the channel with the new input
        $output = $this->runFunctionSnippet('update_channel', [
            self::$projectId,
            self::$location,
            self::$channelId,
            self::$backupInputId
        ]);
        $this->assertStringContainsString(self::$channelName, $output);

        // Check that the channel has an updated input key name
        $livestreamClient = new LivestreamServiceClient();
        $formattedName = $livestreamClient->channelName(
            self::$projectId,
            self::$location,
            self::$channelId
        );
        $channel = $livestreamClient->getChannel($formattedName);
        $inputAttachments = $channel->getInputAttachments();
        foreach ($inputAttachments as $inputAttachment) {
            $this->assertStringContainsString('updated-input', $inputAttachment->getKey());
        }
    }

    /** @depends testUpdateChannel */
    public function testGetChannel()
    {
        $output = $this->runFunctionSnippet('get_channel', [
            self::$projectId,
            self::$location,
            self::$channelId
        ]);
        $this->assertStringContainsString(self::$channelName, $output);
    }

    /** @depends testGetChannel */
    public function testStartChannel()
    {
        $output = $this->runFunctionSnippet('start_channel', [
            self::$projectId,
            self::$location,
            self::$channelId
        ]);
        $this->assertStringContainsString('Started channel', $output);
    }

    /** @depends testStartChannel */
    public function testStopChannel()
    {
        $output = $this->runFunctionSnippet('stop_channel', [
            self::$projectId,
            self::$location,
            self::$channelId
        ]);
        $this->assertStringContainsString('Stopped channel', $output);
    }

    /** @depends testStopChannel */
    public function testDeleteChannel()
    {
        $output = $this->runFunctionSnippet('delete_channel', [
            self::$projectId,
            self::$location,
            self::$channelId
        ]);
        $this->assertStringContainsString('Deleted channel', $output);
    }

    /** @depends testDeleteChannel */
    public function testCreateChannelWithBackupInput()
    {
        self::$channelId = sprintf('%s-%s-%s', self::$channelIdPrefix, uniqid(), time());
        self::$channelName = sprintf('projects/%s/locations/%s/channels/%s', self::$projectId, self::$location, self::$channelId);

        $output = $this->runFunctionSnippet('create_channel_with_backup_input', [
            self::$projectId,
            self::$location,
            self::$channelId,
            self::$inputId,
            self::$backupInputId,
            self::$outputUri
        ]);
        $this->assertStringContainsString(self::$channelName, $output);
    }

    /** @depends testCreateChannelWithBackupInput */
    public function testDeleteChannelWithBackupInput()
    {
        $output = $this->runFunctionSnippet('delete_channel', [
            self::$projectId,
            self::$location,
            self::$channelId
        ]);
        $this->assertStringContainsString('Deleted channel', $output);

        // Delete the update input
        $this->runFunctionSnippet('delete_input', [
            self::$projectId,
            self::$location,
            self::$backupInputId
        ]);

        // Delete the test input
        $this->runFunctionSnippet('delete_input', [
            self::$projectId,
            self::$location,
            self::$inputId
        ]);
    }

    /** @depends testDeleteChannelWithBackupInput */
    public function testCreateChannelEvent()
    {
        // Create a test input for the channel
        self::$inputId = sprintf('%s-%s-%s', self::$inputIdPrefix, uniqid(), time());
        self::$inputName = sprintf('projects/%s/locations/%s/inputs/%s', self::$projectId, self::$location, self::$inputId);

        $this->runFunctionSnippet('create_input', [
            self::$projectId,
            self::$location,
            self::$inputId
        ]);

        // Create a test channel for the event
        self::$channelId = sprintf('%s-%s-%s', self::$channelIdPrefix, uniqid(), time());
        self::$channelName = sprintf('projects/%s/locations/%s/channels/%s', self::$projectId, self::$location, self::$channelId);

        $this->runFunctionSnippet('create_channel', [
            self::$projectId,
            self::$location,
            self::$channelId,
            self::$inputId,
            self::$outputUri
        ]);

        $this->runFunctionSnippet('start_channel', [
            self::$projectId,
            self::$location,
            self::$channelId
        ]);

        self::$eventId = sprintf('%s-%s-%s', self::$eventIdPrefix, uniqid(), time());
        self::$eventName = sprintf('projects/%s/locations/%s/channels/%s/events/%s', self::$projectId, self::$location, self::$channelId, self::$eventId);

        $output = $this->runFunctionSnippet('create_channel_event', [
            self::$projectId,
            self::$location,
            self::$channelId,
            self::$eventId
        ]);
        $this->assertStringContainsString(self::$eventName, $output);
    }

    /** @depends testCreateChannelEvent */
    public function testListChannelEvents()
    {
        $output = $this->runFunctionSnippet('list_channel_events', [
            self::$projectId,
            self::$location,
            self::$channelId
        ]);
        $this->assertStringContainsString(self::$eventName, $output);
    }

    /** @depends testListChannelEvents */
    public function testGetChannelEvent()
    {
        $output = $this->runFunctionSnippet('get_channel_event', [
            self::$projectId,
            self::$location,
            self::$channelId,
            self::$eventId
        ]);
        $this->assertStringContainsString(self::$eventName, $output);
    }

    /** @depends testGetChannelEvent */
    public function testDeleteChannelEvent()
    {
        $output = $this->runFunctionSnippet('delete_channel_event', [
            self::$projectId,
            self::$location,
            self::$channelId,
            self::$eventId
        ]);
        $this->assertStringContainsString('Deleted channel event', $output);
    }

    /** @depends testDeleteChannelEvent */
    public function testDeleteChannelWithEvents()
    {
        $this->runFunctionSnippet('stop_channel', [
            self::$projectId,
            self::$location,
            self::$channelId
        ]);

        $output = $this->runFunctionSnippet('delete_channel', [
            self::$projectId,
            self::$location,
            self::$channelId
        ]);
        $this->assertStringContainsString('Deleted channel', $output);

        // Delete the test input
        $this->runFunctionSnippet('delete_input', [
            self::$projectId,
            self::$location,
            self::$inputId
        ]);
    }

    /** @depends testDeleteChannelWithEvents */
    public function testCreateAsset()
    {
        self::$assetId = sprintf('%s-%s-%s', self::$assetIdPrefix, uniqid(), time());
        self::$assetName = sprintf('projects/%s/locations/%s/assets/%s', self::$projectId, self::$location, self::$assetId);

        $output = $this->runFunctionSnippet('create_asset', [
            self::$projectId,
            self::$location,
            self::$assetId,
            self::$assetUri
        ]);
        $this->assertStringContainsString(self::$assetName, $output);
    }

    /** @depends testCreateAsset */
    public function testListAssets()
    {
        $output = $this->runFunctionSnippet('list_assets', [
            self::$projectId,
            self::$location
        ]);
        $this->assertStringContainsString(self::$assetName, $output);
    }

    /** @depends testListAssets */
    public function testGetAsset()
    {
        $output = $this->runFunctionSnippet('get_asset', [
            self::$projectId,
            self::$location,
            self::$assetId
        ]);
        $this->assertStringContainsString(self::$assetName, $output);
    }

    /** @depends testGetAsset */
    public function testDeleteAsset()
    {
        $output = $this->runFunctionSnippet('delete_asset', [
            self::$projectId,
            self::$location,
            self::$assetId
        ]);
        $this->assertStringContainsString('Deleted asset', $output);
    }

    /** @depends testDeleteAsset */
    public function testGetPool()
    {
        self::$poolId = 'default'; # only 1 pool supported per location
        self::$poolName = sprintf('projects/%s/locations/%s/pools/%s', self::$projectId, self::$location, self::$poolId);

        $output = $this->runFunctionSnippet('get_pool', [
            self::$projectId,
            self::$location,
            self::$poolId
        ]);
        $this->assertStringContainsString(self::$poolName, $output);
    }

    /** @depends testGetPool */
    public function testUpdatePool()
    {
        # You can't update a pool if any channels are running. Updating a pool
        # takes a long time to complete. If tests are running in parallel for
        # different versions of PHP, this test will fail.
        $this->markTestSkipped('Cannot be run if tests run in parallel.');

        $output = $this->runFunctionSnippet('update_pool', [
            self::$projectId,
            self::$location,
            self::$poolId,
            ''
        ]);
        $this->assertStringContainsString(self::$poolName, $output);

        $livestreamClient = new LivestreamServiceClient();
        $formattedName = $livestreamClient->poolName(
            self::$projectId,
            self::$location,
            self::$poolId
        );
        $pool = $livestreamClient->getPool($formattedName);
        $this->assertEquals($pool->getNetworkConfig()->getPeeredNetwork(), '');
    }

    private static function deleteOldInputs(): void
    {
        $livestreamClient = new LivestreamServiceClient();
        $parent = $livestreamClient->locationName(self::$projectId, self::$location);
        $response = $livestreamClient->listInputs($parent);
        $inputs = $response->iterateAllElements();

        $currentTime = time();
        $oneHourInSecs = 60 * 60 * 1;

        foreach ($inputs as $input) {
            $tmp = explode('/', $input->getName());
            $id = end($tmp);
            $tmp = explode('-', $id);
            $timestamp = intval(end($tmp));

            if ($currentTime - $timestamp >= $oneHourInSecs) {
                try {
                    $livestreamClient->deleteInput($input->getName());
                } catch (ApiException $e) {
                    // Cannot delete inputs that are added to channels
                    if ($e->getStatus() === 'FAILED_PRECONDITION') {
                        printf('FAILED_PRECONDITION for %s.', $input->getName());
                        continue;
                    }
                    throw $e;
                }
            }
        }
    }

    private static function deleteOldChannels(): void
    {
        $livestreamClient = new LivestreamServiceClient();
        $parent = $livestreamClient->locationName(self::$projectId, self::$location);
        $response = $livestreamClient->listChannels($parent);
        $channels = $response->iterateAllElements();

        $currentTime = time();
        $oneHourInSecs = 60 * 60 * 1;

        foreach ($channels as $channel) {
            $tmp = explode('/', $channel->getName());
            $id = end($tmp);
            $tmp = explode('-', $id);
            $timestamp = intval(end($tmp));

            if ($currentTime - $timestamp >= $oneHourInSecs) {
                // Must delete channel events before deleting the channel
                $response = $livestreamClient->listEvents($channel->getName());
                $events = $response->iterateAllElements();
                foreach ($events as $event) {
                    try {
                        $livestreamClient->deleteEvent($event->getName());
                    } catch (ApiException $e) {
                        printf('Channel event delete failed: %s.' . PHP_EOL, $e->getMessage());
                    }
                }

                try {
                    $livestreamClient->stopChannel($channel->getName());
                } catch (ApiException $e) {
                    // Cannot delete channels that are running, but
                    // channel may already be stopped
                    if ($e->getStatus() === 'FAILED_PRECONDITION') {
                        printf('FAILED_PRECONDITION for %s.' . PHP_EOL, $channel->getName());
                    } else {
                        throw $e;
                    }
                }

                try {
                    $livestreamClient->deleteChannel($channel->getName());
                } catch (ApiException $e) {
                    // Cannot delete inputs that are added to channels
                    if ($e->getStatus() === 'FAILED_PRECONDITION') {
                        printf('FAILED_PRECONDITION for %s.' . PHP_EOL, $channel->getName());
                        continue;
                    }
                    throw $e;
                }
            }
        }
    }

    private static function deleteOldAssets(): void
    {
        $livestreamClient = new LivestreamServiceClient();
        $parent = $livestreamClient->locationName(self::$projectId, self::$location);
        $response = $livestreamClient->listAssets($parent);
        $assets = $response->iterateAllElements();

        $currentTime = time();
        $oneHourInSecs = 60 * 60 * 1;

        foreach ($assets as $asset) {
            $tmp = explode('/', $asset->getName());
            $id = end($tmp);
            $tmp = explode('-', $id);
            $timestamp = intval(end($tmp));

            if ($currentTime - $timestamp >= $oneHourInSecs) {
                try {
                    $livestreamClient->deleteAsset($asset->getName());
                } catch (ApiException $e) {
                    printf('Asset delete failed: %s.' . PHP_EOL, $e->getMessage());
                }
            }
        }
    }
}
