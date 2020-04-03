<?php
/**
 * Copyright 2020 Google Inc.
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

use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class securityCenterTest extends TestCase
{
    use TestTrait;

    private static $testNotificationCreate;
    private static $testNotificationGet;
    private static $testNotificationUpdate;

    public static function setUpBeforeClass()
    {
        self::$testNotificationCreate = self::randomNotificationId();
        self::$testNotificationGet = self::randomNotificationId();
        self::$testNotificationUpdate = self::randomNotificationId();
    }

    private function deleteConfig(string $configId)
    {
        $deleteOutput = $this->runSnippet('delete_notification', [
            self::getOrganizationId(),
            $configId,
        ]);

        $this->assertContains('Notification config was deleted', $deleteOutput);
    }

    public function testCreateNotification()
    {
        $createOutput = $this->runSnippet('create_notification', [
            self::getOrganizationId(),
            self::$testNotificationCreate,
            self::$projectId,
            self::getTopicName()
        ]);

        $this->assertContains('Notification config was created', $createOutput);

        self::deleteConfig(self::$testNotificationCreate);
    }

    public function testGetNotificationConfig()
    {
        $createOutput = $this->runSnippet('create_notification', [
            self::getOrganizationId(),
            self::$testNotificationGet,
            self::$projectId,
            self::getTopicName()
        ]);

        $this->assertContains('Notification config was created', $createOutput);

        $getOutput = $this->runSnippet('get_notification', [
            self::getOrganizationId(),
            self::$testNotificationGet
        ]);

        $this->assertContains('Notification config was retrieved', $getOutput);

        self::deleteConfig(self::$testNotificationGet);
    }

    public function testUpdateNotificationConfig()
    {
        $createOutput = $this->runSnippet('create_notification', [
            self::getOrganizationId(),
            self::$testNotificationUpdate,
            self::$projectId,
            self::getTopicName()
        ]);

        $this->assertContains('Notification config was created', $createOutput);

        $getOutput = $this->runSnippet('update_notification', [
            self::getOrganizationId(),
            self::$testNotificationUpdate,
            self::$projectId,
            self::getTopicName()
        ]);

        $this->assertContains('Notification config was updated', $getOutput);

        self::deleteConfig(self::$testNotificationUpdate);
    }

    public function testListNotificationConfig()
    {
        $listOutput = $this->runSnippet('list_notification', [
            self::getOrganizationId(),
        ]);

        $this->assertContains('Notification configs were listed', $listOutput);
    }

    private static function getOrganizationId()
    {
        return self::requireEnv('GOOGLE_ORGANIZATION_ID');
    }

    private static function getTopicName()
    {
        return self::requireEnv('GOOGLE_SECURITYCENTER_PUBSUB_TOPIC');
    }

    private static function randomNotificationId()
    {
        return uniqid('php-notification-config-');
    }
}
