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

use Google_Client;
use Google_Service_CloudKMS;
use Google_Service_CloudKMS_DecryptRequest;
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\ExecuteCommandTrait;

class kmsTest extends \PHPUnit_Framework_TestCase
{
    use TestTrait;
    use ExecuteCommandTrait;

    private static $commandFile = __DIR__ . '/../kms.php';
    private static $encryptedFile;
    private static $tempRing;
    private static $tempKey;
    private static $tempVersion;
    private $ring;
    private $key;
    private $altKey;

    public function setUp()
    {
        $this->ring = $this->requireEnv('GOOGLE_KMS_KEYRING');
        $this->key = $this->requireEnv('GOOGLE_KMS_CRYPTOKEY');
        $this->altKey = $this->requireEnv('GOOGLE_KMS_CRYPTOKEY_ALTERNATE');
    }

    public function testEncrypt()
    {
        $infile = __DIR__ . '/data/plaintext.txt';
        $outfile = sys_get_temp_dir() . '/plaintext.txt.encrypted';

        $output = $this->runCommand('encryption', [
            'keyring' => $this->ring,
            'cryptokey' => $this->key,
            'infile' => $infile,
            'outfile' => $outfile,
            '--project' => self::$projectId,
        ]);

        $this->assertTrue(file_exists($outfile));

        // assert the text matches
        $parent = sprintf(
            'projects/%s/locations/global/keyRings/%s/cryptoKeys/%s',
            self::$projectId,
            $this->ring,
            $this->key
        );
        // Instantiate the client, authenticate, and add scopes.
        $client = new Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope('https://www.googleapis.com/auth/cloud-platform');
        $kms = new Google_Service_CloudKMS($client);
        // create the decrypt request
        $request = new Google_Service_CloudKMS_DecryptRequest([
            'ciphertext' => base64_encode(file_get_contents($outfile))
        ]);
        $response = $kms->projects_locations_keyRings_cryptoKeys->decrypt(
            $parent,
            $request
        );
        $this->assertEquals(
            file_get_contents(__DIR__ . '/data/plaintext.txt'),
            base64_decode($response['plaintext'])
        );

        $this->assertContains(sprintf('Saved encrypted text to %s' . PHP_EOL, $outfile), $output);

        self::$encryptedFile = $outfile;
    }

    /** @depends testEncrypt */
    public function testDecrypt()
    {
        $outfile = sys_get_temp_dir() . '/plaintext.txt.decrypted';

        $output = $this->runCommand('encryption', [
            'keyring' => $this->ring,
            'cryptokey' => $this->key,
            'infile' => self::$encryptedFile,
            'outfile' => $outfile,
            '--decrypt' => true,
            '--project' => self::$projectId,
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
        $userEmail = 'betterbrent@google.com';

        $output = $this->runCommand('iam', [
            'keyring' => $this->ring,
            '--user-email' => $userEmail,
            '--project' => self::$projectId,
        ]);

        $this->assertContains(sprintf(
            'Member user:%s added to policy for keyRing %s' . PHP_EOL,
            $userEmail,
            $this->ring
        ), $output);
    }

    /**
     * @depends testAddUserToKeyRing
     */
    public function testRemoveUserFromKeyRing()
    {
        $userEmail = 'betterbrent@google.com';

        $output = $this->runCommand('iam', [
            'keyring' => $this->ring,
            '--user-email' => $userEmail,
            '--remove' => true,
            '--project' => self::$projectId,
        ]);

        $this->assertContains(sprintf(
            'Member user:%s removed from policy for keyRing %s' . PHP_EOL,
            $userEmail,
            $this->ring
        ), $output);
    }

    public function testAddUserToCryptoKey()
    {
        $userEmail = 'betterbrent@google.com';

        $output = $this->runCommand('iam', [
            'keyring' => $this->ring,
            'cryptokey' => $this->key,
            '--user-email' => $userEmail,
            '--project' => self::$projectId,
        ]);

        $this->assertContains(sprintf(
            'Member user:%s added to policy for cryptoKey %s in keyRing %s' . PHP_EOL,
            $userEmail,
            $this->key,
            $this->ring
        ), $output);
    }

    /**
     * @depends testAddUserToCryptoKey
     */
    public function testRemoveUserFromCryptoKey()
    {
        $userEmail = 'betterbrent@google.com';

        $output = $this->runCommand('iam', [
            'keyring' => $this->ring,
            'cryptokey' => $this->key,
            '--user-email' => $userEmail,
            '--remove' => true,
            '--project' => self::$projectId,
        ]);

        $this->assertContains(sprintf(
            'Member user:%s removed from policy for cryptoKey %s in keyRing %s' . PHP_EOL,
            $userEmail,
            $this->key,
            $this->ring
        ), $output);
    }

    public function testAddServiceAccountToCryptoKey()
    {
        $serviceAccountEmail = $this->requireEnv('GOOGLE_KMS_SERVICEACCOUNTEMAIL');

        $output = $this->runCommand('iam', [
            'keyring' => $this->ring,
            'cryptokey' => $this->key,
            '--service-account-email' => $serviceAccountEmail,
            '--project' => self::$projectId,
        ]);

        $this->assertContains(sprintf(
            'Member serviceAccount:%s added to policy for cryptoKey %s in keyRing %s' . PHP_EOL,
            $serviceAccountEmail,
            $this->key,
            $this->ring
        ), $output);
    }

    /**
     * @depends testAddServiceAccountToCryptoKey
     */
    public function testRemoveServiceAccountFromCryptoKey()
    {
        $serviceAccountEmail = $this->requireEnv('GOOGLE_KMS_SERVICEACCOUNTEMAIL');

        $output = $this->runCommand('iam', [
            'keyring' => $this->ring,
            'cryptokey' => $this->key,
            '--service-account-email' => $serviceAccountEmail,
            '--remove' => true,
            '--project' => self::$projectId,
        ]);

        $this->assertContains(sprintf(
            'Member serviceAccount:%s removed from policy for cryptoKey %s in keyRing %s' . PHP_EOL,
            $serviceAccountEmail,
            $this->key,
            $this->ring
        ), $output);
    }

    public function testAddServiceAccountToKeyRing()
    {
        $serviceAccountEmail = $this->requireEnv('GOOGLE_KMS_SERVICEACCOUNTEMAIL');

        $output = $this->runCommand('iam', [
            'keyring' => $this->ring,
            '--service-account-email' => $serviceAccountEmail,
            '--project' => self::$projectId,
        ]);

        $this->assertContains(sprintf(
            'Member serviceAccount:%s added to policy for keyRing %s' . PHP_EOL,
            $serviceAccountEmail,
            $this->ring
        ), $output);
    }

    /**
     * @depends testAddServiceAccountToKeyRing
     */
    public function testRemoveServiceAccountFromKeyRing()
    {
        $serviceAccountEmail = $this->requireEnv('GOOGLE_KMS_SERVICEACCOUNTEMAIL');

        $output = $this->runCommand('iam', [
            'keyring' => $this->ring,
            '--service-account-email' => $serviceAccountEmail,
            '--remove' => true,
            '--project' => self::$projectId,
        ]);

        $this->assertContains(sprintf(
            'Member serviceAccount:%s removed from policy for keyRing %s' . PHP_EOL,
            $serviceAccountEmail,
            $this->ring
        ), $output);
    }

    public function testListCryptoKeys()
    {
        $output = $this->runCommand('key', [
            'keyring' => $this->ring,
            '--project' => self::$projectId,
        ]);

        $this->assertContains('Name: ', $output);
        $this->assertContains('Create Time: ', $output);
        $this->assertContains('Purpose: ', $output);
        $this->assertContains('Primary Version: ', $output);
    }

    public function testCreateCryptoKey()
    {
        self::$tempKey = 'test-crypto-key-' . time();
        $output = $this->runCommand('key', [
            'keyring' => $this->ring,
            'cryptokey' => self::$tempKey,
            '--create' => true,
            '--project' => self::$projectId,
        ]);

        $this->assertContains(sprintf(
            'Created cryptoKey %s in keyRing %s' . PHP_EOL,
            self::$tempKey,
            $this->ring
        ), $output);
    }

    /**
     * @depends testCreateCryptoKey
     */
    public function testGetCryptoKey()
    {
        $output = $this->runCommand('key', [
            'keyring' => $this->ring,
            'cryptokey' => self::$tempKey,
            '--project' => self::$projectId,
        ]);

        $this->assertContains(self::$tempKey, $output);
        $this->assertContains('Create Time: ', $output);
        $this->assertContains('Purpose: ', $output);
        $this->assertContains('Primary Version: ', $output);
    }

    public function testListKeyRings()
    {
        $output = $this->runCommand('keyring', [
            '--project' => self::$projectId,
        ]);

        $this->assertContains('Name: ', $output);
        $this->assertContains('Create Time: ', $output);
    }

    public function testCreateKeyRing()
    {
        self::$tempRing = 'test-key-ring-' . time();
        $output = $this->runCommand('keyring', [
            'keyring' => self::$tempRing,
            '--create' => true,
            '--project' => self::$projectId,
        ]);

        $this->assertContains(sprintf('Created keyRing %s' . PHP_EOL, self::$tempRing), $output);
    }

    /**
     * @depends testCreateKeyRing
     */
    public function testGetKeyRing()
    {
        $output = $this->runCommand('keyring', [
            'keyring' => self::$tempRing,
            '--project' => self::$projectId,
        ]);

        $this->assertContains(self::$tempRing, $output);
        $this->assertContains('Create Time: ', $output);
    }

    public function testListCryptoKeyVersions()
    {
        $output = $this->runCommand('version', [
            'keyring' => $this->ring,
            'cryptokey' => $this->altKey,
            '--project' => self::$projectId,
        ]);

        $this->assertContains('Name: ', $output);
        $this->assertContains('Create Time: ', $output);
        $this->assertContains('State: ', $output);
    }

    public function testCreateCryptoKeyVersion()
    {
        $output = $this->runCommand('version', [
            'keyring' => $this->ring,
            'cryptokey' => $this->altKey,
            '--create' => true,
            '--project' => self::$projectId,
        ]);

        $regex = sprintf(
            '/Created version (\d+) for cryptoKey %s in keyRing %s/' . PHP_EOL,
            $this->altKey,
            $this->ring
        );
        $this->assertEquals(1, preg_match($regex, $output, $matches));
        self::$tempVersion = $matches[1];
    }

    /**
     * @depends testCreateCryptoKeyVersion
     */
    public function testGetCryptoKeyVersions()
    {
        $output = $this->runCommand('version', [
            'keyring' => $this->ring,
            'cryptokey' => $this->altKey,
            'version' => self::$tempVersion,
            '--project' => self::$projectId,
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
        $output = $this->runCommand('version', [
            'keyring' => $this->ring,
            'cryptokey' => $this->altKey,
            'version' => self::$tempVersion,
            '--disable' => true,
            '--project' => self::$projectId,
        ]);

        $this->assertContains(sprintf(
            'Disabled version %s for cryptoKey %s in keyRing %s' . PHP_EOL,
            self::$tempVersion,
            $this->altKey,
            $this->ring
        ), $output);
    }

    /**
     * @depends testDisableCryptoKeyVersion
     */
    public function testEnableCryptoKeyVersion()
    {
        $output = $this->runCommand('version', [
            'keyring' => $this->ring,
            'cryptokey' => $this->altKey,
            'version' => self::$tempVersion,
            '--enable' => true,
            '--project' => self::$projectId,
        ]);

        $this->assertContains(sprintf(
            'Enabled version %s for cryptoKey %s in keyRing %s' . PHP_EOL,
            self::$tempVersion,
            $this->altKey,
            $this->ring
        ), $output);
    }

    /**
     * @depends testCreateCryptoKeyVersion
     */
    public function testDestroyCryptoKeyVersion()
    {
        $output = $this->runCommand('version', [
            'keyring' => $this->ring,
            'cryptokey' => $this->altKey,
            'version' => self::$tempVersion,
            '--destroy' => true,
            '--project' => self::$projectId,
        ]);

        $this->assertContains(sprintf(
            'Destroyed version %s for cryptoKey %s in keyRing %s' . PHP_EOL,
            self::$tempVersion,
            $this->altKey,
            $this->ring
        ), $output);
    }

    /**
     * @depends testDestroyCryptoKeyVersion
     */
    public function testRestoreCryptoKeyVersion()
    {
        $output = $this->runCommand('version', [
            'keyring' => $this->ring,
            'cryptokey' => $this->altKey,
            'version' => self::$tempVersion,
            '--restore' => true,
            '--project' => self::$projectId,
        ]);

        $this->assertContains(sprintf(
            'Restored version %s for cryptoKey %s in keyRing %s' . PHP_EOL,
            self::$tempVersion,
            $this->altKey,
            $this->ring
        ), $output);
    }

    /**
     * @depends testCreateCryptoKeyVersion
     */
    public function testSetPrimaryCryptoKeyVersion()
    {
        $output = $this->runCommand('version', [
            'keyring' => $this->ring,
            'cryptokey' => $this->altKey,
            'version' => self::$tempVersion,
            '--set-primary' => true,
            '--project' => self::$projectId,
        ]);

        $this->assertContains(sprintf(
            'Set %s as primary version for cryptoKey %s in keyRing %s' . PHP_EOL,
            self::$tempVersion,
            $this->altKey,
            $this->ring
        ), $output);
    }
}
