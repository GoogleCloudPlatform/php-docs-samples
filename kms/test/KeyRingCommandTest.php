<?php
/**
 * Copyright 2017 Google Inc.
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

namespace Google\Cloud\Samples\Kms;

use Symfony\Component\Console\Tester\CommandTester;

class KeyRingCommandTest extends \PHPUnit_Framework_TestCase
{
    private $commandTester;
    private $projectId;
    private static $ring;

    public function setUp()
    {
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            return $this->markTestSkipped('Set the GOOGLE_PROJECT_ID environment variable');
        }

        $this->projectId = $projectId;

        $application = require __DIR__ . '/../kms.php';
        $this->commandTester = new CommandTester($application->get('keyring'));
    }

    public function testListKeyRings()
    {
        $this->commandTester->execute(
            [
                '--project' => $this->projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex('/Name: /');
        $this->expectOutputRegex('/Create Time: /');
    }

    public function testCreateKeyRing()
    {
        self::$ring = 'test-key-ring-' . time();
        $this->commandTester->execute(
            [
                'keyring' => self::$ring,
                '--create' => true,
                '--project' => $this->projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputString(sprintf('Created keyRing %s' . PHP_EOL, self::$ring));
    }

    /**
     * @depends testCreateKeyRing
     */
    public function testGetKeyRing()
    {
        $this->commandTester->execute(
            [
                'keyring' => self::$ring,
                '--project' => $this->projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex(sprintf('/Name: %s/', self::$ring));
        $this->expectOutputRegex('/Create Time: /');
    }
}
