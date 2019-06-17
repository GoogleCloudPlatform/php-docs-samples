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

use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class kmsTest extends TestCase
{
    use TestTrait;

    private static $locationId = 'global';
    private static $userEmail = 'betterbrent@google.com';
    private static $encryptedFile;
    private static $tempRing;
    private static $tempKey;
    private static $tempVersion;
    private static $ring;
    private static $key;
    private static $altKey;

    public static function setUpBeforeClass()
    {
        self::$ring = self::requireEnv('GOOGLE_KMS_KEYRING');
        self::$key = self::requireEnv('GOOGLE_KMS_CRYPTOKEY');
        self::$altKey = self::requireEnv('GOOGLE_KMS_CRYPTOKEY_ALTERNATE');
    }

    public function testEncrypt()
    {
        $infile = __DIR__ . '/data/plaintext.txt';
        $outfile = sys_get_temp_dir() . '/plaintext.txt.encrypted';

        $output = $this->runSnippet('encrypt', [
            self::$ring,
            self::$key,
            $infile,
            $outfile
        ]);

        $this->assertTrue(file_exists($outfile));

        $this->assertContains(sprintf('Saved encrypted text to %s' . PHP_EOL, $outfile), $output);

        self::$encryptedFile = $outfile;
    }

    /** @depends testEncrypt */
    public function testDecrypt()
    {
        $outfile = sys_get_temp_dir() . '/plaintext.txt.decrypted';

        $output = $this->runSnippet('decrypt', [
            self::$ring,
            self::$key,
            self::$encryptedFile,
            $outfile
        ]);

        $this->assertTrue(file_exists($outfile));
        $this->assertEquals(
            file_get_contents(__DIR__ . '/data/plaintext.txt'),
            file_get_contents($outfile)
        );

        $this->assertContains(sprintf('Saved decrypted text to %s' . PHP_EOL, $outfile), $output);
    }

    public function testAddUserToKeyRing()
    {
        $output = $this->runSnippet('add_member_to_keyring_policy', [
            self::$ring,
            'user:' . self::$userEmail,
            'roles/cloudkms.cryptoKeyEncrypterDecrypter'
        ]);

        $this->assertContains(sprintf(
            'Member user:%s added to policy for keyRing %s' . PHP_EOL,
            self::$userEmail,
            self::$ring
        ), $output);
    }

    /**
     * @depends testAddUserToKeyRing
     */
    public function testGetKeyRingPolicy()
    {
        $output = $this->runSnippet('get_keyring_policy', [
            self::$ring,
        ]);

        $this->assertContains('user:' . self::$userEmail, $output);
    }

    /**
     * @depends testAddUserToKeyRing
     */
    public function testRemoveUserFromKeyRing()
    {
        $output = $this->runSnippet('remove_member_from_keyring_policy', [
            self::$ring,
            'user:' . self::$userEmail,
            'roles/cloudkms.cryptoKeyEncrypterDecrypter'
        ]);

        $this->assertContains(sprintf(
            'Member user:%s removed from policy for keyRing %s' . PHP_EOL,
            self::$userEmail,
            self::$ring
        ), $output);
    }

    public function testAddUserToCryptoKey()
    {
        $output = $this->runSnippet('add_member_to_cryptokey_policy', [
            self::$ring,
            self::$key,
            'user:' . self::$userEmail,
            'roles/cloudkms.cryptoKeyEncrypterDecrypter'
        ]);

        $this->assertContains(sprintf(
            'Member user:%s added to policy for cryptoKey %s in keyRing %s' . PHP_EOL,
            self::$userEmail,
            self::$key,
            self::$ring
        ), $output);
    }

    /**
     * @depends testAddUserToCryptoKey
     */
    public function testGetCryptoKeyPolicy()
    {
        $output = $this->runSnippet('get_cryptokey_policy', [
            self::$ring,
            self::$key,
        ]);

        $this->assertContains('user:' . self::$userEmail, $output);
    }

    /**
     * @depends testAddUserToCryptoKey
     */
    public function testRemoveUserFromCryptoKey()
    {
        $output = $this->runSnippet('remove_member_from_cryptokey_policy', [
            self::$ring,
            self::$key,
            'user:' . self::$userEmail,
            'roles/cloudkms.cryptoKeyEncrypterDecrypter'
        ]);

        $this->assertContains(sprintf(
            'Member user:%s removed from policy for cryptoKey %s in keyRing %s' . PHP_EOL,
            self::$userEmail,
            self::$key,
            self::$ring
        ), $output);
    }

    public function testAddServiceAccountToCryptoKey()
    {
        $serviceAccountEmail = $this->requireEnv('GOOGLE_KMS_SERVICEACCOUNTEMAIL');

        $output = $this->runSnippet('add_member_to_cryptokey_policy', [
            self::$ring,
            self::$key,
            'serviceAccount:' . $serviceAccountEmail,
            'roles/cloudkms.cryptoKeyEncrypterDecrypter'
        ]);

        $this->assertContains(sprintf(
            'Member serviceAccount:%s added to policy for cryptoKey %s in keyRing %s' . PHP_EOL,
            $serviceAccountEmail,
            self::$key,
            self::$ring
        ), $output);
    }

    /**
     * @depends testAddServiceAccountToCryptoKey
     */
    public function testRemoveServiceAccountFromCryptoKey()
    {
        $serviceAccountEmail = $this->requireEnv('GOOGLE_KMS_SERVICEACCOUNTEMAIL');

        $output = $this->runSnippet('remove_member_from_cryptokey_policy', [
            self::$ring,
            self::$key,
            'serviceAccount:' . $serviceAccountEmail,
            'roles/cloudkms.cryptoKeyEncrypterDecrypter'
        ]);

        $this->assertContains(sprintf(
            'Member serviceAccount:%s removed from policy for cryptoKey %s in keyRing %s' . PHP_EOL,
            $serviceAccountEmail,
            self::$key,
            self::$ring
        ), $output);
    }

    public function testAddServiceAccountToKeyRing()
    {
        $serviceAccountEmail = $this->requireEnv('GOOGLE_KMS_SERVICEACCOUNTEMAIL');

        $output = $this->runSnippet('add_member_to_keyring_policy', [
            self::$ring,
            'serviceAccount:' . $serviceAccountEmail,
            'roles/cloudkms.cryptoKeyEncrypterDecrypter'
        ]);

        $this->assertContains(sprintf(
            'Member serviceAccount:%s added to policy for keyRing %s' . PHP_EOL,
            $serviceAccountEmail,
            self::$ring
        ), $output);
    }

    /**
     * @depends testAddServiceAccountToKeyRing
     */
    public function testRemoveServiceAccountFromKeyRing()
    {
        $serviceAccountEmail = $this->requireEnv('GOOGLE_KMS_SERVICEACCOUNTEMAIL');

        $output = $this->runSnippet('remove_member_from_keyring_policy', [
            self::$ring,
            'serviceAccount:' . $serviceAccountEmail,
            'roles/cloudkms.cryptoKeyEncrypterDecrypter'
        ]);

        $this->assertContains(sprintf(
            'Member serviceAccount:%s removed from policy for keyRing %s' . PHP_EOL,
            $serviceAccountEmail,
            self::$ring
        ), $output);
    }

    public function testListCryptoKeys()
    {
        $output = $this->runSnippet('list_cryptokeys', [
            self::$ring
        ]);

        $this->assertContains('Name: ', $output);
        $this->assertContains('Create Time: ', $output);
        $this->assertContains('Purpose: ', $output);
        $this->assertContains('Primary Version: ', $output);
    }

    public function testCreateCryptoKey()
    {
        self::$tempKey = 'test-crypto-key-' . time();
        $output = $this->runSnippet('create_cryptokey', [
            self::$ring,
            self::$tempKey
        ]);

        $this->assertContains(sprintf(
            'Created cryptoKey %s in keyRing %s' . PHP_EOL,
            self::$tempKey,
            self::$ring
        ), $output);
    }

    /**
     * @depends testCreateCryptoKey
     */
    public function testGetCryptoKey()
    {
        $output = $this->runSnippet('get_cryptokey', [
            self::$ring,
            self::$tempKey
        ]);

        $this->assertContains(self::$tempKey, $output);
        $this->assertContains('Create Time: ', $output);
        $this->assertContains('Purpose: ', $output);
        $this->assertContains('Primary Version: ', $output);
    }

    public function testListKeyRings()
    {
        $output = $this->runSnippet('list_keyrings');

        $this->assertContains('Name: ', $output);
        $this->assertContains('Create Time: ', $output);
    }

    public function testCreateKeyRing()
    {
        self::$tempRing = 'test-key-ring-' . time();
        $output = $this->runSnippet('create_keyring', [
            self::$tempRing,
        ]);

        $this->assertContains(sprintf('Created keyRing %s' . PHP_EOL, self::$tempRing), $output);
    }

    /**
     * @depends testCreateKeyRing
     */
    public function testGetKeyRing()
    {
        $output = $this->runSnippet('get_keyring', [
            self::$tempRing,
        ]);

        $this->assertContains(self::$tempRing, $output);
        $this->assertContains('Create Time: ', $output);
    }

    public function testListCryptoKeyVersions()
    {
        $output = $this->runSnippet('list_cryptokey_versions', [
            self::$ring,
            self::$altKey
        ]);

        $this->assertContains('Name: ', $output);
        $this->assertContains('Create Time: ', $output);
        $this->assertContains('State: ', $output);
    }

    public function testCreateCryptoKeyVersion()
    {
        $output = $this->runSnippet('create_cryptokey_version', [
            self::$ring,
            self::$altKey,
        ]);

        $regex = sprintf(
            '/Created version (\d+) for cryptoKey %s in keyRing %s/' . PHP_EOL,
            self::$altKey,
            self::$ring
        );
        $this->assertEquals(1, preg_match($regex, $output, $matches));
        self::$tempVersion = $matches[1];
    }

    /**
     * @depends testCreateCryptoKeyVersion
     */
    public function testGetCryptoKeyVersions()
    {
        $output = $this->runSnippet('get_cryptokey_version', [
            self::$ring,
            self::$altKey,
            self::$tempVersion,
        ]);

        $this->assertContains('Name: ', $output);
        $this->assertContains('Create Time: ', $output);
        $this->assertContains('State: ', $output);
    }

    /**
     * @depends testCreateCryptoKeyVersion
     */
    public function testDisableCryptoKeyVersion()
    {
        $output = $this->runSnippet('disable_cryptokey_version', [
            self::$ring,
            self::$altKey,
            self::$tempVersion,
        ]);

        $this->assertContains(sprintf(
            'Disabled version %s for cryptoKey %s in keyRing %s' . PHP_EOL,
            self::$tempVersion,
            self::$altKey,
            self::$ring
        ), $output);
    }

    /**
     * @depends testDisableCryptoKeyVersion
     */
    public function testEnableCryptoKeyVersion()
    {
        $output = $this->runSnippet('enable_cryptokey_version', [
            self::$ring,
            self::$altKey,
            self::$tempVersion,
        ]);

        $this->assertContains(sprintf(
            'Enabled version %s for cryptoKey %s in keyRing %s' . PHP_EOL,
            self::$tempVersion,
            self::$altKey,
            self::$ring
        ), $output);
    }

    /**
     * @depends testCreateCryptoKeyVersion
     */
    public function testDestroyCryptoKeyVersion()
    {
        $output = $this->runSnippet('destroy_cryptokey_version', [
            self::$ring,
            self::$altKey,
            self::$tempVersion,
        ]);

        $this->assertContains(sprintf(
            'Destroyed version %s for cryptoKey %s in keyRing %s' . PHP_EOL,
            self::$tempVersion,
            self::$altKey,
            self::$ring
        ), $output);
    }

    /**
     * @depends testDestroyCryptoKeyVersion
     */
    public function testRestoreCryptoKeyVersion()
    {
        $output = $this->runSnippet('restore_cryptokey_version', [
            self::$ring,
            self::$altKey,
            self::$tempVersion,
        ]);

        $this->assertContains(sprintf(
            'Restored version %s for cryptoKey %s in keyRing %s' . PHP_EOL,
            self::$tempVersion,
            self::$altKey,
            self::$ring
        ), $output);
    }

    /**
     * @depends testCreateCryptoKeyVersion
     */
    public function testSetPrimaryCryptoKeyVersion()
    {
        $output = $this->runSnippet('set_cryptokey_primary_version', [
            self::$ring,
            self::$altKey,
            self::$tempVersion,
        ]);

        $this->assertContains(sprintf(
            'Set %s as primary version for cryptoKey %s in keyRing %s' . PHP_EOL,
            self::$tempVersion,
            self::$altKey,
            self::$ring
        ), $output);
    }

    private function runSnippet($sampleName, $params = [])
    {
        $argv = array_merge([0, self::$projectId, self::$locationId], $params);
        ob_start();
        require __DIR__ . "/../src/$sampleName.php";
        return ob_get_clean();
    }
}
