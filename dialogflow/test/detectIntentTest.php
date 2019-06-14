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
 * Unit Tests for detect intent commands.
 */
class detectIntentTest extends TestCase
{
    use DialogflowTestTrait;

    private static $audioFilePath;
    private static $texts = ['hello', 'book a meeting room', 'mountain view'];

    public static function setUpBeforeClass()
    {
        self::$audioFilePath = realpath(__DIR__ . '/../resources/book_a_room.wav');
    }

    public function testDetectText()
    {
        $output = $this->runCommand('detect-intent-texts', [
            'texts' => self::$texts
        ]);

        $this->assertContains('date', $output);
    }

    public function testDetectAudio()
    {
        $output = $this->runCommand('detect-intent-audio', [
            'path' => self::$audioFilePath
        ]);

        $this->assertContains('would you like to reserve a room', $output);
    }

    public function testDetectStream()
    {
        if (!extension_loaded('grpc')) {
            $this->markTestSkipped('Must enable grpc extension.');
        }
        $output = $this->runCommand('detect-intent-stream', [
            'path' => self::$audioFilePath
        ]);

        $this->assertContains('would you like to reserve a room', $output);
    }
}
