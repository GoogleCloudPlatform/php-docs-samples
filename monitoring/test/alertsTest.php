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
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class alertsTest extends TestCase
{
    use ExecuteCommandTrait;
    use TestTrait;

    private static $commandFile = __DIR__ . '/../alerts.php';
    private static $policyId;
    private static $channelId;

    public function testCreatePolicy()
    {
        $regexp = '/^Created alert policy projects\/[\w-]+\/alertPolicies\/(\d+)$/';
        $output = $this->runAlertCommand('create-policy');
        $this->assertRegexp($regexp, $output);

        // Save the policy ID for later
        preg_match($regexp, $output, $matches);
        self::$policyId = $matches[1];
    }

    public function testEnablePolicies()
    {
        $policyName = AlertPolicyServiceClient::alertPolicyName(self::$projectId, self::$policyId);
        $output = $this->runAlertCommand('enable-policies', [
            'filter' => sprintf('name = "%s"', $policyName),
            'enable' => true,
        ]);
        $this->assertContains(
            sprintf('Policy %s is already enabled', $policyName),
            $output
        );

        $output = $this->runAlertCommand('enable-policies', [
            'filter' => sprintf('name = "%s"', $policyName),
            'enable' => false,
        ]);

        $this->assertContains(sprintf('Disabled %s', $policyName), $output);
    }

    /** @depends testCreatePolicy */
    public function testCreateChannel()
    {
        $regexp = '/^Created notification channel projects\/[\w-]+\/notificationChannels\/(\d+)$/';
        $output = $this->runAlertCommand('create-channel');
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
        $output = $this->runAlertCommand('create-channel');
        $this->assertRegexp($regexp, $output);
        preg_match($regexp, $output, $matches);
        $channelId1 = $matches[1];

        $output = $this->runAlertCommand('create-channel');
        $this->assertRegexp($regexp, $output);
        preg_match($regexp, $output, $matches);
        $channelId2 = $matches[1];

        $output = $this->runAlertCommand('replace-channels', [
            'policy_id' => self::$policyId,
            'channel_id' => [$channelId1, $channelId2]
        ]);
        $this->assertContains(sprintf('Updated %s', $policyName), $output);

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

        $output = $this->runAlertCommand('replace-channels', [
            'policy_id' => self::$policyId,
            'channel_id' => self::$channelId,
        ]);
        $this->assertContains(sprintf('Updated %s', $policyName), $output);

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
        $output = $this->runAlertCommand('list-policies');
        $this->assertContains(self::$policyId, $output);
    }

    /** @depends testCreateChannel */
    public function testListChannels()
    {
        // backup
        $output = $this->runAlertCommand('list-channels');
        $this->assertContains(self::$channelId, $output);
    }

    /** @depends testCreateChannel */
    public function testBackupAndRestore()
    {
        // backup
        $output = $this->runAlertCommand('backup-policies');
        $this->assertContains('Backed up alert policies', $output);

        $backupJson = file_get_contents(__DIR__ . '/../backup.json');
        $backup = json_decode($backupJson, true);
        $this->assertArrayHasKey('policies', $backup);
        $this->assertArrayHasKey('channels', $backup);
        $this->assertGreaterThan(0, count($backup['policies']));
        $this->assertGreaterThan(0, count($backup['channels']));
        $this->assertContains(self::$policyId, $backupJson);
        $this->assertContains(self::$channelId, $backupJson);

        // restore
        $output = $this->runAlertCommand('restore-policies');
        $this->assertContains('Restored alert policies', $output);
    }

    /** @depends testCreatePolicy */
    public function testDeleteChannel()
    {
        // delete the policy first (required in order to delete the channel)
        $alertClient = new AlertPolicyServiceClient();
        $alertClient->deleteAlertPolicy(
            $alertClient->alertPolicyName(self::$projectId, self::$policyId)
        );

        $output = $this->runAlertCommand('delete-channel', [
            'channel_id' => self::$channelId,
        ]);
        $this->assertContains('Deleted notification channel', $output);
        $this->assertContains(self::$channelId, $output);
    }

    public function runAlertCommand($command, $args = [])
    {
        return $this->runCommand($command, $args + [
            'project_id' => self::$projectId
        ]);
    }
}
