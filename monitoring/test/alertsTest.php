<?php
/**
 * Copyright 2018 Google Inc.
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

namespace Google\Cloud\Samples\Monitoring;

use Google\Cloud\Monitoring\V3\AlertPolicyServiceClient;
use Google\Cloud\Monitoring\V3\NotificationChannelServiceClient;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;
use PHPUnitRetry\RetryTrait;

class alertsTest extends TestCase
{
    use TestTrait;
    use RetryTrait;

    private static $policyId;
    private static $channelId;

    public function testCreatePolicy()
    {
        $regexp = '/^Created alert policy projects\/[\w-]+\/alertPolicies\/(\d+)$/';
        $output = $this->runFunctionSnippet('alert_create_policy', [
            'projectId' => self::$projectId,
        ]);
        $this->assertRegexp($regexp, $output);

        // Save the policy ID for later
        preg_match($regexp, $output, $matches);
        self::$policyId = $matches[1];
    }

    /**
     * @depends testCreatePolicy
     * @retryAttempts 2
     * @retryDelaySeconds 10
     */
    public function testEnablePolicies()
    {
        $policyName = AlertPolicyServiceClient::alertPolicyName(
            self::$projectId,
            self::$policyId
        );
        $output = $this->runFunctionSnippet('alert_enable_policies', [
            'projectId' => self::$projectId,
            'filter' => sprintf('name = "%s"', $policyName),
            'enable' => true,
        ]);
        $this->assertStringContainsString(
            sprintf('Policy %s is already enabled', $policyName),
            $output
        );
    }

    /**
     * @depends testEnablePolicies
     */
    public function testDisablePolicies()
    {
        $policyName = AlertPolicyServiceClient::alertPolicyName(
            self::$projectId,
            self::$policyId
        );
        $output = $this->runFunctionSnippet('alert_enable_policies', [
            'projectId' => self::$projectId,
            'filter' => sprintf('name = "%s"', $policyName),
            'enable' => false,
        ]);
        $this->assertStringContainsString(
            sprintf('Disabled %s', $policyName),
            $output
        );
    }

    /** @depends testCreatePolicy */
    public function testCreateChannel()
    {
        $regexp = '/^Created notification channel projects\/[\w-]+\/notificationChannels\/(\d+)$/';
        $output = $this->runFunctionSnippet('alert_create_channel', [
            'projectId' => self::$projectId,
        ]);
        $this->assertRegexp($regexp, $output);

        // Save the channel ID for later
        preg_match($regexp, $output, $matches);
        self::$channelId = $matches[1];
    }

    /** @depends testCreateChannel */
    public function testReplaceChannel()
    {
        $alertClient = new AlertPolicyServiceClient();
        $channelClient = new NotificationChannelServiceClient();
        $policyName = $alertClient->alertPolicyName(self::$projectId, self::$policyId);

        $regexp = '/^Created notification channel projects\/[\w-]+\/notificationChannels\/(\d+)$/';
        $output = $this->runFunctionSnippet('alert_create_channel', [
            'projectId' => self::$projectId,
        ]);
        $this->assertRegexp($regexp, $output);
        preg_match($regexp, $output, $matches);
        $channelId1 = $matches[1];

        $output = $this->runFunctionSnippet('alert_create_channel', [
            'projectId' => self::$projectId,
        ]);
        $this->assertRegexp($regexp, $output);
        preg_match($regexp, $output, $matches);
        $channelId2 = $matches[1];

        $output = $this->runFunctionSnippet('alert_replace_channels', [
            'projectId' => self::$projectId,
            'alertPolicyId' => self::$policyId,
            'channelIds' => [$channelId1, $channelId2]
        ]);
        $this->assertStringContainsString(sprintf('Updated %s', $policyName), $output);

        // verify the new channels have been added to the policy
        $policy = $alertClient->getAlertPolicy($policyName);
        $channels = $policy->getNotificationChannels();
        $this->assertEquals(2, count($channels));
        $this->assertEquals(
            $newChannelName1 = $channelClient->notificationChannelName(self::$projectId, $channelId1),
            $channels[0]
        );
        $this->assertEquals(
            $newChannelName2 = $channelClient->notificationChannelName(self::$projectId, $channelId2),
            $channels[1]
        );

        $output = $this->runFunctionSnippet('alert_replace_channels', [
            'projectId' => self::$projectId,
            'alertPolicyId' => self::$policyId,
            'channelIds' => [self::$channelId],
        ]);
        $this->assertStringContainsString(sprintf('Updated %s', $policyName), $output);

        // verify the new channel replaces the previous channels added to the policy
        $policy = $alertClient->getAlertPolicy($policyName);
        $channels = $policy->getNotificationChannels();
        $this->assertEquals(1, count($channels));
        $this->assertEquals(
            $channelClient->notificationChannelName(self::$projectId, self::$channelId),
            $channels[0]
        );

        // remove the old chnnels
        $channelClient->deleteNotificationChannel($newChannelName1);
        $channelClient->deleteNotificationChannel($newChannelName2);
    }

    /** @depends testCreatePolicy */
    public function testListPolciies()
    {
        // backup
        $output = $this->runFunctionSnippet('alert_list_policies', [
            'projectId' => self::$projectId,
        ]);
        $this->assertStringContainsString(self::$policyId, $output);
    }

    /** @depends testCreateChannel */
    public function testListChannels()
    {
        // backup
        $output = $this->runFunctionSnippet('alert_list_channels', [
            'projectId' => self::$projectId,
        ]);
        $this->assertStringContainsString(self::$channelId, $output);
    }

    /**
     * @depends testCreateChannel
     */
    public function testBackupPolicies()
    {
        $output = $this->runFunctionSnippet('alert_backup_policies', [
            'projectId' => self::$projectId,
        ]);
        $this->assertStringContainsString('Backed up alert policies', $output);

        $backupJson = file_get_contents(__DIR__ . '/../backup.json');
        $backup = json_decode($backupJson, true);
        $this->assertArrayHasKey('policies', $backup);
        $this->assertArrayHasKey('channels', $backup);
        $this->assertGreaterThan(0, count($backup['policies']));
        $this->assertGreaterThan(0, count($backup['channels']));
        $this->assertStringContainsString(self::$policyId, $backupJson);
        $this->assertStringContainsString(self::$channelId, $backupJson);
    }

    /**
     * @depends testBackupPolicies
     * @retryAttempts 3
     * @retryDelaySeconds 10
     */
    public function testRestorePolicies()
    {
        $output = $this->runFunctionSnippet('alert_restore_policies', [
            'projectId' => self::$projectId,
        ]);
        $this->assertStringContainsString('Restored alert policies', $output);
    }

    /** @depends testCreatePolicy */
    public function testDeleteChannel()
    {
        // delete the policy first (required in order to delete the channel)
        $alertClient = new AlertPolicyServiceClient();
        $alertClient->deleteAlertPolicy(
            $alertClient->alertPolicyName(self::$projectId, self::$policyId)
        );

        $output = $this->runFunctionSnippet('alert_delete_channel', [
            'projectId' => self::$projectId,
            'channelId' => self::$channelId,
        ]);
        $this->assertStringContainsString('Deleted notification channel', $output);
        $this->assertStringContainsString(self::$channelId, $output);
    }
}
