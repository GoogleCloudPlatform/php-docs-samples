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

class IamCommandTest extends \PHPUnit_Framework_TestCase
{
    private $commandTester;
    private $projectId;
    private $ring;
    private $key;

    public function setUp()
    {
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            return $this->markTestSkipped('Set the GOOGLE_PROJECT_ID environment variable');
        }
        if (!$ring = getenv('GOOGLE_KMS_KEYRING')) {
            return $this->markTestSkipped('Set the GOOGLE_KMS_KEYRING environment variable');
        }
        if (!$key = getenv('GOOGLE_KMS_CRYPTOKEY')) {
            return $this->markTestSkipped('Set the GOOGLE_KMS_CRYPTOKEY environment variable');
        }

        $this->projectId = $projectId;
        $this->ring = $ring;
        $this->key = $key;

        $application = require __DIR__ . '/../kms.php';
        $this->commandTester = new CommandTester($application->get('iam'));
    }

    public function testAddUserToKeyRing()
    {
        $userEmail = 'betterbrent@google.com';

        $this->commandTester->execute(
            [
                'keyring' => $this->ring,
                '--user-email' => $userEmail,
                '--project' => $this->projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputString(sprintf(
            'Member user:%s added to policy for keyRing %s' . PHP_EOL,
            $userEmail,
            $this->ring
        ));
    }

    /**
     * @depends testAddUserToKeyRing
     */
    public function testRemoveUserFromKeyRing()
    {
        $userEmail = 'betterbrent@google.com';

        $this->commandTester->execute(
            [
                'keyring' => $this->ring,
                '--user-email' => $userEmail,
                '--remove' => true,
                '--project' => $this->projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputString(sprintf(
            'Member user:%s removed from policy for keyRing %s' . PHP_EOL,
            $userEmail,
            $this->ring
        ));
    }

    public function testAddUserToCryptoKey()
    {
        $userEmail = 'betterbrent@google.com';

        $this->commandTester->execute(
            [
                'keyring' => $this->ring,
                'cryptokey' => $this->key,
                '--user-email' => $userEmail,
                '--project' => $this->projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputString(sprintf(
            'Member user:%s added to policy for cryptoKey %s in keyRing %s' . PHP_EOL,
            $userEmail,
            $this->key,
            $this->ring
        ));
    }

    /**
     * @depends testAddUserToCryptoKey
     */
    public function testRemoveUserFromCryptoKey()
    {
        $userEmail = 'betterbrent@google.com';

        $this->commandTester->execute(
            [
                'keyring' => $this->ring,
                'cryptokey' => $this->key,
                '--user-email' => $userEmail,
                '--remove' => true,
                '--project' => $this->projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputString(sprintf(
            'Member user:%s removed from policy for cryptoKey %s in keyRing %s' . PHP_EOL,
            $userEmail,
            $this->key,
            $this->ring
        ));
    }

    public function testAddServiceAccountToCryptoKey()
    {
        if (!$serviceAccountEmail = getenv('GOOGLE_KMS_SERVICEACCOUNTEMAIL')) {
            return $this->markTestSkipped('Set the GOOGLE_KMS_SERVICEACCOUNTEMAIL environment variable');
        }

        $this->commandTester->execute(
            [
                'keyring' => $this->ring,
                'cryptokey' => $this->key,
                '--service-account-email' => $serviceAccountEmail,
                '--project' => $this->projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputString(sprintf(
            'Member serviceAccount:%s added to policy for cryptoKey %s in keyRing %s' . PHP_EOL,
            $serviceAccountEmail,
            $this->key,
            $this->ring
        ));
    }

    /**
     * @depends testAddServiceAccountToCryptoKey
     */
    public function testRemoveServiceAccountFromCryptoKey()
    {
        if (!$serviceAccountEmail = getenv('GOOGLE_KMS_SERVICEACCOUNTEMAIL')) {
            return $this->markTestSkipped('Set the GOOGLE_KMS_SERVICEACCOUNTEMAIL environment variable');
        }

        $this->commandTester->execute(
            [
                'keyring' => $this->ring,
                'cryptokey' => $this->key,
                '--service-account-email' => $serviceAccountEmail,
                '--remove' => true,
                '--project' => $this->projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputString(sprintf(
            'Member serviceAccount:%s removed from policy for cryptoKey %s in keyRing %s' . PHP_EOL,
            $serviceAccountEmail,
            $this->key,
            $this->ring
        ));
    }

    public function testAddServiceAccountToKeyRing()
    {
        if (!$serviceAccountEmail = getenv('GOOGLE_KMS_SERVICEACCOUNTEMAIL')) {
            return $this->markTestSkipped('Set the GOOGLE_KMS_SERVICEACCOUNTEMAIL environment variable');
        }

        $this->commandTester->execute(
            [
                'keyring' => $this->ring,
                '--service-account-email' => $serviceAccountEmail,
                '--project' => $this->projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputString(sprintf(
            'Member serviceAccount:%s added to policy for keyRing %s' . PHP_EOL,
            $serviceAccountEmail,
            $this->ring
        ));
    }

    /**
     * @depends testAddServiceAccountToKeyRing
     */
    public function testRemoveServiceAccountFromKeyRing()
    {
        if (!$serviceAccountEmail = getenv('GOOGLE_KMS_SERVICEACCOUNTEMAIL')) {
            return $this->markTestSkipped('Set the GOOGLE_KMS_SERVICEACCOUNTEMAIL environment variable');
        }

        $this->commandTester->execute(
            [
                'keyring' => $this->ring,
                '--service-account-email' => $serviceAccountEmail,
                '--remove' => true,
                '--project' => $this->projectId,
            ],
            ['interactive' => false]
        );

        $this->expectOutputString(sprintf(
            'Member serviceAccount:%s removed from policy for keyRing %s' . PHP_EOL,
            $serviceAccountEmail,
            $this->ring
        ));
    }
}
