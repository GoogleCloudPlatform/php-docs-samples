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
 * Unit Tests for entity management commands.
 */
class entityTest extends TestCase
{
    use DialogflowTestTrait;

    private static $entityTypeId = 'e57238e2-e692-44ea-9216-6be1b2332e2a';
    private static $entityValue1 = 'fake_entit_for_testing_1';
    private static $entityValue2 = 'fake_entit_for_testing_2';
    private static $synonyms = ['fake_synonym_for_testing_1', 'fake_synonym_for_testing_2'];

    public function testCreateEntity()
    {
        $this->runCommand('entity-create', [
            'entity-value' => self::$entityValue1,
            'entity-type-id' => self::$entityTypeId,
        ]);
        $this->runCommand('entity-create', [
            'entity-value' => self::$entityValue2,
            'synonyms' => self::$synonyms,
            'entity-type-id' => self::$entityTypeId,
        ]);
        $output = $this->runCommand('entity-list', [
            'entity-type-id' => self::$entityTypeId,
        ]);

        $this->assertContains(self::$entityValue1, $output);
        $this->assertContains(self::$entityValue2, $output);
        foreach (self::$synonyms as $synonym) {
            $this->assertContains($synonym, $output);
        }
    }

    /** @depends testCreateEntity */
    public function testDeleteEntity()
    {
        $this->runCommand('entity-delete', [
            'entity-value' => self::$entityValue1,
            'entity-type-id' => self::$entityTypeId
        ]);
        $this->runCommand('entity-delete', [
            'entity-value' => self::$entityValue2,
            'entity-type-id' => self::$entityTypeId
        ]);
        $output = $this->runCommand('entity-list', [
            'entity-type-id' => self::$entityTypeId,
        ]);

        $this->assertNotContains(self::$entityValue1, $output);
        $this->assertNotContains(self::$entityValue2, $output);
    }
}
