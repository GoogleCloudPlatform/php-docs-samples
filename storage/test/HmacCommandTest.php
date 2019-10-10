<?php
/**
 * Copyright 2019 Google Inc.
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

namespace Google\Cloud\Samples\Storage\Tests;

use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for HmacCommand.
 */
class HmacCommandTest extends TestCase
{
    use TestTrait;

    protected $commandTesterList;
    protected $commandTesterCreate;
    protected $commandTesterManage;
    protected $storage;
    protected $hmacServiceAccount;
    protected $accessId;

    public function setUp()
    {
        $application = require __DIR__ . '/../storage.php';
        $this->commandTesterList = new CommandTester($application->get('hmac-sa-list'));
        $this->commandTesterCreate = new CommandTester($application->get('hmac-sa-create'));
        $this->commandTesterManage = new CommandTester($application->get('hmac-sa-manage'));
        $this->storage = new StorageClient();
        $this->hmacServiceAccount = self::$projectId . '@appspot.gserviceaccount.com';
        // Delete all HMAC keys.
        $this->deleteAllHmacKeys($this->hmacServiceAccount);
        // Create test key.
        $hmacKeyCreated = $this->storage->createHmacKey($this->hmacServiceAccount, ['projectId' => self::$projectId]);
        $this->accessId = $hmacKeyCreated->hmacKey()->accessId();
        $this->setOutputCallback(function () {
            // disable output
        });
    }

    public function tearDown()
    {
        // Delete all HMAC keys.
        $this->deleteAllHmacKeys($this->hmacServiceAccount);
    }

    private function deleteAllHmacKeys($serviceAccountEmail)
    {
        $hmacKeys = $this->storage->hmacKeys(['serviceAccountEmail' => $serviceAccountEmail]);
        foreach ($hmacKeys as $hmacKey) {
            if ($hmacKey->info()['state'] == 'ACTIVE') {
                $hmacKey->update('INACTIVE');
            }
            $hmacKey->delete();
        }
    }

    public function testHmacKeyList()
    {
        $this->commandTesterList->execute(
          [
              'projectId' => self::$projectId
          ],
          ['interactive' => false]);
        $this->assertContains('HMAC Key\'s:', $this->getActualOutput());
    }

    public function testHmacKeyCreate()
    {
        $this->commandTesterCreate->execute(
        [
            'projectId' => self::$projectId,
            'serviceAccountEmail' => $this->hmacServiceAccount
        ],
        ['interactive' => false]);
        $this->assertContains('The base64 encoded secret is:', $this->getActualOutput());
    }

    public function testHmacKeyGet()
    {
        $this->commandTesterManage->execute(
        [
            'projectId' => self::$projectId,
            'accessId' => $this->accessId,
            '--get' => true
        ],
        ['interactive' => false]);
        $this->assertContains('HMAC key Metadata:', $this->getActualOutput());
    }

    public function testHmacKeyDeactivate()
    {
        $this->commandTesterManage->execute(
        [
            'projectId' => self::$projectId,
            'accessId' => $this->accessId,
            '--deactivate' => true
        ],
        ['interactive' => false]);
        $this->assertContains('The HMAC key is now inactive', $this->getActualOutput());
    }

    public function testHmacKeyActivate()
    {
        $this->commandTesterManage->execute(
        [
            'projectId' => self::$projectId,
            'accessId' => $this->accessId,
            '--deactivate' => true
        ],
        ['interactive' => false]);
        $this->commandTesterManage->execute(
          [
              'projectId' => self::$projectId,
              'accessId' => $this->accessId,
              '--activate' => true
          ],
          ['interactive' => false]);
        $this->assertContains('The HMAC key is now active', $this->getActualOutput());
    }

    public function testHmacKeyDelete()
    {
        $this->commandTesterManage->execute(
        [
            'projectId' => self::$projectId,
            'accessId' => $this->accessId,
            '--deactivate' => true
        ],
        ['interactive' => false]);
        $this->commandTesterManage->execute(
        [
            'projectId' => self::$projectId,
            'accessId' => $this->accessId,
            '--delete' => true
        ],
        ['interactive' => false]);
        $this->assertContains('The key is deleted,', $this->getActualOutput());
    }
}
