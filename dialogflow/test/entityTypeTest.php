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

namespace Google\Cloud\Samples\Dialogflow;

use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for entity type management commands.
 */
class entityTypeTest extends TestCase
{
    use DialogflowTestTrait;

    private static $entityTypeDisplayName;

    public function setUp()
    {
        self::$entityTypeDisplayName = sprintf('fake_display_%s_%s', rand(100, 999), time());
    }

    public function testCreateEntityType()
    {
        $response = $this->runCommand('entity-type-create', [
            'display-name' => self::$entityTypeDisplayName
        ]);
        $output = $this->runCommand('entity-type-list');

        $this->assertContains(self::$entityTypeDisplayName, $output);

        $response = str_replace(array("\r", "\n"), '', $response);
        $response = explode('/', $response);
        $entityTypeId = end($response);
        return $entityTypeId;
    }

    /** @depends testCreateEntityType */
    public function testDeleteEntityType($entityTypeId)
    {
        $this->runCommand('entity-type-delete', [
            'entity-type-id' => $entityTypeId
        ]);
        $output = $this->runCommand('entity-type-list');

        $this->assertNotContains(self::$entityTypeDisplayName, $output);
    }
}
