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
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for HMAC.
 */
class HmacTest extends TestCase
{
    use TestTrait;

    protected $storage;
    protected $hmacServiceAccount;
    protected $accessId;

    public function setUp(): void
    {
        $this->storage = new StorageClient();
        $this->hmacServiceAccount = self::$projectId . '@appspot.gserviceaccount.com';
        // Delete all HMAC keys.
        $this->deleteAllHmacKeys($this->hmacServiceAccount);
        // Create test key.
        $hmacKeyCreated = $this->storage->createHmacKey($this->hmacServiceAccount, ['projectId' => self::$projectId]);
        $this->accessId = $hmacKeyCreated->hmacKey()->accessId();
    }

    public function tearDown(): void
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
        $output = self::runFunctionSnippet('list_hmac_keys', [
            self::$projectId,
        ]);

        $this->assertStringContainsString('HMAC Key\'s:', $output);
    }

    public function testHmacKeyCreate()
    {
        $output = self::runFunctionSnippet('create_hmac_key', [
            self::$projectId,
            $this->hmacServiceAccount,
        ]);

        $this->assertStringContainsString('The base64 encoded secret is:', $output);
    }

    public function testHmacKeyGet()
    {
        $output = self::runFunctionSnippet('get_hmac_key', [
            self::$projectId,
            $this->accessId,
        ]);

        $this->assertStringContainsString('HMAC key Metadata:', $output);
    }

    public function testHmacKeyDeactivate()
    {
        $output = self::runFunctionSnippet('deactivate_hmac_key', [
            self::$projectId,
            $this->accessId,
        ]);

        $this->assertStringContainsString('The HMAC key is now inactive', $output);
    }

    public function testHmacKeyActivate()
    {
        self::runFunctionSnippet('deactivate_hmac_key', [
            self::$projectId,
            $this->accessId,
        ]);

        $output = self::runFunctionSnippet('activate_hmac_key', [
            self::$projectId,
            $this->accessId,
        ]);

        $this->assertStringContainsString('The HMAC key is now active', $output);
    }

    public function testHmacKeyDelete()
    {
        self::runFunctionSnippet('deactivate_hmac_key', [
            self::$projectId,
            $this->accessId,
        ]);

        $output = self::runFunctionSnippet('delete_hmac_key', [
            self::$projectId,
            $this->accessId,
        ]);

        $this->assertStringContainsString('The key is deleted,', $output);
    }
}
