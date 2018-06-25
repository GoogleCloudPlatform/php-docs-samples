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

/**
 * Unit Tests for intent management commands.
 */
class intentTest extends \PHPUnit_Framework_TestCase
{
    use DialogflowTestTrait;

    private static $displayName;
    private static $messageTexts = ['fake_message_for_testing_1', 'fake_message_for_testing_2'];
    private static $trainingPhraseParts = ['fake_phrase_1', 'fake_phrase_2'];

    public static function setUpBeforeClass()
    {
        self::$displayName = 'fake_display_name_for_testing_' . time();
    }

    public function testCreateIntent()
    {
        $response = $this->runCommand('intent-create', [
            'display-name' => self::$displayName,
            '--training-phrases-parts' => self::$trainingPhraseParts,
            '--message-texts' => self::$messageTexts
        ]);
        $output = $this->runCommand('intent-list');

        $this->assertContains(self::$displayName, $output);

        $response = str_replace(array("\r", "\n"), '', $response);
        $response = explode('/', $response);
        $intentId = end($response);
        return $intentId;
    }

    /** @depends testCreateIntent */
    public function testDeleteIntent($intentId)
    {
        $this->runCommand('intent-delete', [
            'intent-id' => $intentId
        ]);
        $output = $this->runCommand('intent-list');

        $this->assertNotContains(self::$displayName, $output);
    }
}
