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

class securitycenterTest extends TestCase
{
    use TestTrait;

    public function testCreateNotification()
    {
        $createOutput = $this->runSnippet('create_notification', [
            self::getOrganizationId(),
            "php-notification-config-create",
            self::getProject(),
            self::getTopicName()
        ]);

        $this->assertContains('Notification config was created', $createOutput);

        $deleteOutput = $this->runSnippet('delete_notification', [
            self::getOrganizationId(),
            "php-notification-config-create",
        ]);

        $this->assertContains('Notification config was deleted', $deleteOutput);
    }

    public function testGetNotificationConfig()
    {
        $createOutput = $this->runSnippet('create_notification', [
            self::getOrganizationId(),
            "php-notification-config-get",
            self::getProject(),
            self::getTopicName()
        ]);

        $this->assertContains('Notification config was created', $createOutput);

        $getOutput = $this->runSnippet('get_notification', [
            self::getOrganizationId(),
            "php-notification-config-get",
        ]);

        $this->assertContains('Notification config was retrieved', $getOutput);

        $deleteOutput = $this->runSnippet('delete_notification', [
            self::getOrganizationId(),
            "php-notification-config-get",
        ]);

        $this->assertContains('Notification config was deleted', $deleteOutput);

    }

    public function testUpdateNotificationConfig()
    {
        $createOutput = $this->runSnippet('create_notification', [
            self::getOrganizationId(),
            "php-notification-config-update",
            self::getProject(),
            self::getTopicName()
        ]);

        $this->assertContains('Notification config was created', $createOutput);

        $getOutput = $this->runSnippet('update_notification', [
            self::getOrganizationId(),
            "php-notification-config-update",
            self::getProject(),
            self::getTopicName()
        ]);

        $this->assertContains('Notification config was updated', $getOutput);

        $deleteOutput = $this->runSnippet('delete_notification', [
            self::getOrganizationId(),
            "php-notification-config-update",
        ]);

        $this->assertContains('Notification config was deleted', $deleteOutput);

    }

    public function testListNotificationConfig()
    {
        $listOutput = $this->runSnippet('list_notification', [
            self::getOrganizationId(),
        ]);

        $this->assertContains('Notification configs were listed', $listOutput);

    }

    private static function getOrganizationId() {
        return "1081635000895";
    }

    private static function getProject() {
        return "project-a-id";
    }

    private static function getTopicName() {
        return "notifications-sample-topic";
    }
}