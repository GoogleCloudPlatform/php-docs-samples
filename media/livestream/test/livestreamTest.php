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

    public static function setUpBeforeClass(): void
    {
        self::checkProjectEnvVars();
        self::$projectId = self::requireEnv('GOOGLE_PROJECT_ID');

        self::deleteOldChannels();
        self::deleteOldInputs();
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
                try {
                    $livestreamClient->stopChannel($channel->getName());
                } catch (ApiException $e) {
                    // Cannot delete channels that are running, but
                    // channel may already be stopped
                    if ($e->getStatus() === 'FAILED_PRECONDITION') {
                        printf('FAILED_PRECONDITION for %s.', $channel->getName());
                        continue;
                    }
                    throw $e;
                }

                try {
                    $livestreamClient->deleteChannel($channel->getName());
                } catch (ApiException $e) {
                    // Cannot delete inputs that are added to channels
                    if ($e->getStatus() === 'FAILED_PRECONDITION') {
                        printf('FAILED_PRECONDITION for %s.', $channel->getName());
                        continue;
                    }
                    throw $e;
                }
            }
        }
    }
}
