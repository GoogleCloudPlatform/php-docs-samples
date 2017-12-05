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

class KeyCommandTest extends \PHPUnit_Framework_TestCase
{
    private $commandTester;
    private $projectId;
    private $ring;
    private static $key;

    public function setUp()
    {
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            return $this->markTestSkipped('Set the GOOGLE_PROJECT_ID environment variable');
        }
        if (!$ring = getenv('GOOGLE_KMS_KEYRING')) {
            return $this->markTestSkipped('Set the GOOGLE_KMS_KEYRING environment variable');
        }
        $this->projectId = $projectId;
        $this->ring = $ring;

        $application = require __DIR__ . '/../kms.php';
        $this->commandTester = new CommandTester($application->get('key'));
    }

    public function testListCryptoKeys()
    {
        $this->commandTester->execute(
            [
                'keyring' => $this->ring,
                '--project' => $this->projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex('/Name: /');
        $this->expectOutputRegex('/Create Time: /');
        $this->expectOutputRegex('/Purpose: /');
        $this->expectOutputRegex('/Primary Version: /');
    }

    public function testCreateCryptoKey()
    {
        if (!$this->ring) {
            return $this->markTestSkipped('Set the GOOGLE_KMS_KEYRING environment variable');
        }

        self::$key = 'test-crypto-key-' . time();
        $this->commandTester->execute(
            [
                'keyring' => $this->ring,
                'cryptokey' => self::$key,
                '--create' => true,
                '--project' => $this->projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputString(sprintf(
            'Created cryptoKey %s in keyRing %s' . PHP_EOL,
            self::$key,
            $this->ring
        ));
    }

    /**
     * @depends testCreateCryptoKey
     */
    public function testGetCryptoKey()
    {
        $this->commandTester->execute(
            [
                'keyring' => $this->ring,
                'cryptokey' => self::$key,
                '--project' => $this->projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex(sprintf('/Name: %s/', self::$key));
        $this->expectOutputRegex('/Create Time: /');
        $this->expectOutputRegex('/Purpose: /');
        $this->expectOutputRegex('/Primary Version: /');
    }
}
