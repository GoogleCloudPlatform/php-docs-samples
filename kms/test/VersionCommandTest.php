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

class VersionCommandTest extends \PHPUnit_Framework_TestCase
{
    private $commandTester;
    private $projectId;
    private $ring;
    private $key;
    private static $version;

    public function setUp()
    {
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            return $this->markTestSkipped('Set the GOOGLE_PROJECT_ID environment variable');
        }
        if (!$ring = getenv('GOOGLE_KMS_KEYRING')) {
            return $this->markTestSkipped('Set the GOOGLE_KMS_KEYRING environment variable');
        }
        if (!$key = getenv('GOOGLE_KMS_CRYPTOKEY_ALTERNATE')) {
            return $this->markTestSkipped('Set the GOOGLE_KMS_CRYPTOKEY_ALTERNATE environment variable');
        }

        $this->projectId = $projectId;
        $this->ring = $ring;
        $this->key = $key;

        $application = require __DIR__ . '/../kms.php';
        $this->commandTester = new CommandTester($application->get('version'));
    }

    public function testListCryptoKeyVersions()
    {
        $this->commandTester->execute(
            [
                'keyring' => $this->ring,
                'cryptokey' => $this->key,
                '--project' => $this->projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex('/Name: /');
        $this->expectOutputRegex('/Create Time: /');
        $this->expectOutputRegex('/State: /');
    }

    public function testCreateCryptoKeyVersion()
    {
        ob_start();
        $this->commandTester->execute(
            [
                'keyring' => $this->ring,
                'cryptokey' => $this->key,
                '--create' => true,
                '--project' => $this->projectId,
            ],
            ['interactive' => false]
        );
        $output = ob_get_clean();

        $regex = sprintf(
            '/Created version (\d+) for cryptoKey %s in keyRing %s/' . PHP_EOL,
            $this->key,
            $this->ring
        );
        $this->assertEquals(1, preg_match($regex, $output, $matches));
        self::$version = $matches[1];
    }

    /**
     * @depends testCreateCryptoKeyVersion
     */
    public function testGetCryptoKeyVersions()
    {
        $this->commandTester->execute(
            [
                'keyring' => $this->ring,
                'cryptokey' => $this->key,
                'version' => self::$version,
                '--project' => $this->projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex('/Name: /');
        $this->expectOutputRegex('/Create Time: /');
        $this->expectOutputRegex('/State: /');
    }

    /**
     * @depends testCreateCryptoKeyVersion
     */
    public function testDisableCryptoKeyVersion()
    {
        $this->commandTester->execute(
            [
                'keyring' => $this->ring,
                'cryptokey' => $this->key,
                'version' => self::$version,
                '--disable' => true,
                '--project' => $this->projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputString(sprintf(
            'Disabled version %s for cryptoKey %s in keyRing %s' . PHP_EOL,
            self::$version,
            $this->key,
            $this->ring
        ));
    }

    /**
     * @depends testDisableCryptoKeyVersion
     */
    public function testEnableCryptoKeyVersion()
    {
        $this->commandTester->execute(
            [
                'keyring' => $this->ring,
                'cryptokey' => $this->key,
                'version' => self::$version,
                '--enable' => true,
                '--project' => $this->projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputString(sprintf(
            'Enabled version %s for cryptoKey %s in keyRing %s' . PHP_EOL,
            self::$version,
            $this->key,
            $this->ring
        ));
    }

    /**
     * @depends testCreateCryptoKeyVersion
     */
    public function testDestroyCryptoKeyVersion()
    {
        $this->commandTester->execute(
            [
                'keyring' => $this->ring,
                'cryptokey' => $this->key,
                'version' => self::$version,
                '--destroy' => true,
                '--project' => $this->projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputString(sprintf(
            'Destroyed version %s for cryptoKey %s in keyRing %s' . PHP_EOL,
            self::$version,
            $this->key,
            $this->ring
        ));
    }

    /**
     * @depends testDestroyCryptoKeyVersion
     */
    public function testRestoreCryptoKeyVersion()
    {
        $this->commandTester->execute(
            [
                'keyring' => $this->ring,
                'cryptokey' => $this->key,
                'version' => self::$version,
                '--restore' => true,
                '--project' => $this->projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputString(sprintf(
            'Restored version %s for cryptoKey %s in keyRing %s' . PHP_EOL,
            self::$version,
            $this->key,
            $this->ring
        ));
    }

    /**
     * @depends testCreateCryptoKeyVersion
     */
    public function testSetPrimaryCryptoKeyVersion()
    {
        $this->commandTester->execute(
            [
                'keyring' => $this->ring,
                'cryptokey' => $this->key,
                'version' => self::$version,
                '--set-primary' => true,
                '--project' => $this->projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputString(sprintf(
            'Set %s as primary version for cryptoKey %s in keyRing %s' . PHP_EOL,
            self::$version,
            $this->key,
            $this->ring
        ));
    }
}
