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
 * Unit Tests for context management commands.
 */
class contextTest extends TestCase
{
    use DialogflowTestTrait;

    private static $contextId;
    private static $sessionId = 'fake_session_for_testing';

    public static function setUpBeforeClass()
    {
        self::$contextId = sprintf('fake_context_%s_%s', rand(100, 999), time());
    }

    public function testCreateContext()
    {
        $this->runCommand('context-create', [
            'context-id' => self::$contextId,
            '--session-id' => self::$sessionId,
        ]);
        $output = $this->runCommand('context-list', [
            '--session-id' => self::$sessionId,
        ]);

        $this->assertContains(self::$contextId, $output);
    }

    /** @depends testCreateContext */
    public function testDeleteContext()
    {
        $this->runCommand('context-delete', [
            'context-id' => self::$contextId,
            '--session-id' => self::$sessionId,
        ]);
        $output = $this->runCommand('context-list', [
            '--session-id' => self::$sessionId,
        ]);

        $this->assertNotContains(self::$contextId, $output);
    }
}
