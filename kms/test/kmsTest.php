<?php
/*
 * Copyright 2020 Google LLC.
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

declare(strict_types=1);

namespace Google\Cloud\Samples\Kms;

use Google\Cloud\Iam\V1\Binding;
use Google\Cloud\Kms\V1\CryptoKey;
use Google\Cloud\Kms\V1\CryptoKey\CryptoKeyPurpose;
use Google\Cloud\Kms\V1\CryptoKeyVersion\CryptoKeyVersionAlgorithm;
use Google\Cloud\Kms\V1\CryptoKeyVersion\CryptoKeyVersionState;
use Google\Cloud\Kms\V1\CryptoKeyVersionTemplate;
use Google\Cloud\Kms\V1\Digest;
use Google\Cloud\Kms\V1\KeyManagementServiceClient;
use Google\Cloud\Kms\V1\KeyRing;
use Google\Cloud\Kms\V1\ProtectionLevel;
use Google\Cloud\TestUtils\TestTrait;

use PHPUnit\Framework\TestCase;
use Google\Protobuf\FieldMask;

class kmsTest extends TestCase
{
    use TestTrait;

    private static $locationId;
    private static $keyRingId;
    private static $asymmetricDecryptKeyId;
    private static $asymmetricSignEcKeyId;
    private static $asymmetricSignRsaKeyId;
    private static $hsmKeyId;
    private static $symmetricKeyId;

    public static function setUpBeforeClass()
    {
        self::$locationId = 'us-east1';

        self::$keyRingId = self::randomId();
        self::createKeyRing(self::$keyRingId);

        self::$asymmetricDecryptKeyId = self::randomId();
        self::createAsymmetricDecryptKey(self::$asymmetricDecryptKeyId);

        self::$asymmetricSignEcKeyId = self::randomId();
        self::createAsymmetricSignEcKey(self::$asymmetricSignEcKeyId);

        self::$asymmetricSignRsaKeyId = self::randomId();
        self::createAsymmetricSignRsaKey(self::$asymmetricSignRsaKeyId);

        self::$hsmKeyId = self::randomId();
        self::createHsmKey(self::$hsmKeyId);

        self::$symmetricKeyId = self::randomId();
        self::createSymmetricKey(self::$symmetricKeyId);
    }

    public static function tearDownAfterClass()
    {
        $client = new KeyManagementServiceClient();

        $keyRingName = $client->keyRingName(self::$projectId, self::$locationId, self::$keyRingId);
        $keys = $client->listCryptoKeys($keyRingName);
        foreach ($keys as $key) {
            if ($key->getRotationPeriod() || $key->getNextRotationTime()) {
                $updatedKey = (new CryptoKey())
                    ->setName($key->getName());

                $updateMask = (new FieldMask)
                    ->setPaths(['rotation_period', 'next_rotation_time']);

                $client->updateCryptoKey($updatedKey, $updateMask);
            }

            $versions = $client->listCryptoKeyVersions($key->getName(), [
                'filter' => 'state != DESTROYED AND state != DESTROY_SCHEDULED',
            ]);
            foreach ($versions as $version) {
                $client->destroyCryptoKeyVersion($version->getName());
            }
        }
    }

    private static function randomId()
    {
        return uniqid('php-snippets-');
    }

    private static function createKeyRing(string $id)
    {
        $client = new KeyManagementServiceClient();
        $locationName = $client->locationName(self::$projectId, self::$locationId);
        $keyRing = new KeyRing();
        return $client->createKeyRing($locationName, $id, $keyRing);
    }

    private static function createAsymmetricDecryptKey(string $id)
    {
        $client = new KeyManagementServiceClient();
        $keyRingName = $client->keyRingName(self::$projectId, self::$locationId, self::$keyRingId);
        $key = (new CryptoKey())
            ->setPurpose(CryptoKeyPurpose::ASYMMETRIC_DECRYPT)
            ->setVersionTemplate((new CryptoKeyVersionTemplate)
                ->setAlgorithm(CryptoKeyVersionAlgorithm::RSA_DECRYPT_OAEP_2048_SHA256))
            ->setLabels(['foo' => 'bar', 'zip' => 'zap']);
        return self::waitForReady($client->createCryptoKey($keyRingName, $id, $key));
    }

    private static function createAsymmetricSignEcKey(string $id)
    {
        $client = new KeyManagementServiceClient();
        $keyRingName = $client->keyRingName(self::$projectId, self::$locationId, self::$keyRingId);
        $key = (new CryptoKey())
            ->setPurpose(CryptoKeyPurpose::ASYMMETRIC_SIGN)
            ->setVersionTemplate((new CryptoKeyVersionTemplate)
                ->setAlgorithm(CryptoKeyVersionAlgorithm::EC_SIGN_P256_SHA256))
            ->setLabels(['foo' => 'bar', 'zip' => 'zap']);
        return self::waitForReady($client->createCryptoKey($keyRingName, $id, $key));
    }

    private static function createAsymmetricSignRsaKey(string $id)
    {
        $client = new KeyManagementServiceClient();
        $keyRingName = $client->keyRingName(self::$projectId, self::$locationId, self::$keyRingId);
        $key = (new CryptoKey())
            ->setPurpose(CryptoKeyPurpose::ASYMMETRIC_SIGN)
            ->setVersionTemplate((new CryptoKeyVersionTemplate)
                ->setAlgorithm(CryptoKeyVersionAlgorithm::RSA_SIGN_PSS_2048_SHA256))
            ->setLabels(['foo' => 'bar', 'zip' => 'zap']);
        return self::waitForReady($client->createCryptoKey($keyRingName, $id, $key));
    }

    private static function createHsmKey(string $id)
    {
        $client = new KeyManagementServiceClient();
        $keyRingName = $client->keyRingName(self::$projectId, self::$locationId, self::$keyRingId);
        $key = (new CryptoKey())
            ->setPurpose(CryptoKeyPurpose::ENCRYPT_DECRYPT)
            ->setVersionTemplate((new CryptoKeyVersionTemplate)
                ->setProtectionLevel(ProtectionLevel::HSM)
                ->setAlgorithm(CryptoKeyVersionAlgorithm::GOOGLE_SYMMETRIC_ENCRYPTION))
            ->setLabels(['foo' => 'bar', 'zip' => 'zap']);
        return self::waitForReady($client->createCryptoKey($keyRingName, $id, $key));
    }

    private static function createSymmetricKey(string $id)
    {
        $client = new KeyManagementServiceClient();
        $keyRingName = $client->keyRingName(self::$projectId, self::$locationId, self::$keyRingId);
        $key = (new CryptoKey())
            ->setPurpose(CryptoKeyPurpose::ENCRYPT_DECRYPT)
            ->setVersionTemplate((new CryptoKeyVersionTemplate)
                ->setAlgorithm(CryptoKeyVersionAlgorithm::GOOGLE_SYMMETRIC_ENCRYPTION))
            ->setLabels(['foo' => 'bar', 'zip' => 'zap']);
        return self::waitForReady($client->createCryptoKey($keyRingName, $id, $key));
    }

    private static function waitForReady(CryptoKey $key)
    {
        $client = new KeyManagementServiceClient();
        $versionName = $key->getName() . '/cryptoKeyVersions/1';
        $version = $client->getCryptoKeyVersion($versionName);
        $attempts = 0;
        while ($version->getState() != CryptoKeyVersionState::ENABLED) {
            if ($attempts > 10) {
                $msg = sprintf('key version %s was not ready after 10 attempts', $versionName);
                throw new Exception($msg);
            }
            usleep(500);
            $version = $client->getCryptoKeyVersion($versionName);
            $attempts += 1;
        }
        return $key;
    }

    private static function runSample($sampleName, $params = [])
    {
        $sampleFile = $sampleName . '.php';
        $sampleName = str_replace('_sample', '', $sampleName);
        return self::runSampleFile($sampleFile, $sampleName, $params);
    }

    private static function runSampleFile($sampleFile, $sampleName, $params = [])
    {
        $sampleFile = __DIR__ . sprintf('/../src/%s', $sampleFile);
        $sampleName = str_replace('_sample', '', $sampleName) . '_sample';
        $fn = function () use ($sampleFile, $sampleName, $params) {
            try {
                ob_start();
                require $sampleFile;
                $result = call_user_func_array($sampleName, $params);
                return array($result, ob_get_clean());
            } catch (\Exception $e) {
                ob_get_clean();
                throw $e;
            }
        };

        return $fn();
    }

    public function testCreateKeyAsymmetricDecrypt()
    {
        list($key, $output) = $this->runSample('create_key_asymmetric_decrypt', [
            self::$projectId,
            self::$locationId,
            self::$keyRingId,
            self::randomId()
        ]);

        $this->assertContains('Created asymmetric decryption key', $output);
        $this->assertEquals(CryptoKeyPurpose::ASYMMETRIC_DECRYPT, $key->getPurpose());
        $this->assertEquals(CryptoKeyVersionAlgorithm::RSA_DECRYPT_OAEP_2048_SHA256, $key->getVersionTemplate()->getAlgorithm());
    }

    public function testCreateKeyAsymmetricSign()
    {
        list($key, $output) = $this->runSample('create_key_asymmetric_sign', [
            self::$projectId,
            self::$locationId,
            self::$keyRingId,
            self::randomId()
        ]);

        $this->assertContains('Created asymmetric signing key', $output);
        $this->assertEquals(CryptoKeyPurpose::ASYMMETRIC_SIGN, $key->getPurpose());
        $this->assertEquals(CryptoKeyVersionAlgorithm::RSA_SIGN_PKCS1_2048_SHA256, $key->getVersionTemplate()->getAlgorithm());
    }

    public function testCreateKeyHsm()
    {
        list($key, $output) = $this->runSample('create_key_hsm', [
          self::$projectId,
          self::$locationId,
          self::$keyRingId,
          self::randomId()
        ]);

        $this->assertContains('Created hsm key', $output);
        $this->assertEquals(ProtectionLevel::HSM, $key->getVersionTemplate()->getProtectionLevel());
    }

    public function testCreateKeyLabels()
    {
        list($key, $output) = $this->runSample('create_key_labels', [
          self::$projectId,
          self::$locationId,
          self::$keyRingId,
          self::randomId()
        ]);

        $this->assertContains('Created labeled key', $output);
        $this->assertEquals('alpha', $key->getLabels()['team']);
        $this->assertEquals('cc1234', $key->getLabels()['cost_center']);
    }

    public function testCreateKeyRing()
    {
        list($keyRing, $output) = $this->runSample('create_key_ring', [
          self::$projectId,
          self::$locationId,
          self::randomId()
        ]);

        $this->assertContains('Created key ring', $output);
        $this->assertContains(self::$locationId, $keyRing->getName());
    }

    public function testCreateKeyRotationSchedule()
    {
        list($key, $output) = $this->runSample('create_key_rotation_schedule', [
          self::$projectId,
          self::$locationId,
          self::$keyRingId,
          self::randomId()
        ]);

        $this->assertContains('Created key with rotation', $output);
        $this->assertEquals(2592000, $key->getRotationPeriod()->getSeconds());
    }

    public function testCreateKeySymmetricEncryptDecrypt()
    {
        list($key, $output) = $this->runSample('create_key_symmetric_encrypt_decrypt', [
            self::$projectId,
            self::$locationId,
            self::$keyRingId,
            self::randomId()
        ]);

        $this->assertContains('Created symmetric key', $output);
        $this->assertEquals(CryptoKeyPurpose::ENCRYPT_DECRYPT, $key->getPurpose());
        $this->assertEquals(CryptoKeyVersionAlgorithm::GOOGLE_SYMMETRIC_ENCRYPTION, $key->getVersionTemplate()->getAlgorithm());
    }

    public function testCreateKeyVersion()
    {
        list($version, $output) = $this->runSample('create_key_version', [
            self::$projectId,
            self::$locationId,
            self::$keyRingId,
            self::$symmetricKeyId
        ]);

        $this->assertContains('Created key version', $output);
        $this->assertContains(self::$symmetricKeyId, $version->getName());
    }

    public function testDecryptAsymmetric()
    {
        // PHP does not currently support custom MGF, so this sample is just a
        // comment.
        $this->assertTrue(true);
    }

    public function testDecryptSymmetric()
    {
        $plaintext = 'my message';

        $client = new KeyManagementServiceClient();
        $keyName = $client->cryptoKeyName(self::$projectId, self::$locationId, self::$keyRingId, self::$symmetricKeyId);
        $ciphertext = $client->encrypt($keyName, $plaintext)->getCiphertext();

        list($response, $output) = $this->runSample('decrypt_symmetric', [
            self::$projectId,
            self::$locationId,
            self::$keyRingId,
            self::$symmetricKeyId,
            $ciphertext
        ]);

        $this->assertContains('Plaintext', $output);
        $this->assertEquals($plaintext, $response->getPlaintext());
    }

    public function testDestroyRestoreKeyVersion()
    {
        list($version, $output) = $this->runSample('destroy_key_version', [
            self::$projectId,
            self::$locationId,
            self::$keyRingId,
            self::$symmetricKeyId,
            '1'
        ]);

        $this->assertContains('Destroyed key version', $output);
        $this->assertContains($version->getState(), array(
            CryptoKeyVersionState::DESTROYED,
            CryptoKeyVersionState::DESTROY_SCHEDULED,
        ));

        list($version, $output) = $this->runSample('restore_key_version', [
            self::$projectId,
            self::$locationId,
            self::$keyRingId,
            self::$symmetricKeyId,
            '1'
        ]);

        $this->assertContains('Restored key version', $output);
        $this->assertEquals(CryptoKeyVersionState::DISABLED, $version->getState());
    }

    public function testDisableEnableKeyVersion()
    {
        list($version, $output) = $this->runSample('disable_key_version', [
            self::$projectId,
            self::$locationId,
            self::$keyRingId,
            self::$symmetricKeyId,
            '1'
        ]);

        $this->assertContains('Disabled key version', $output);
        $this->assertEquals(CryptoKeyVersionState::DISABLED, $version->getState());

        list($version, $output) = $this->runSample('enable_key_version', [
            self::$projectId,
            self::$locationId,
            self::$keyRingId,
            self::$symmetricKeyId,
            '1'
        ]);

        $this->assertContains('Enabled key version', $output);
        $this->assertEquals(CryptoKeyVersionState::ENABLED, $version->getState());
    }

    public function testEncryptAsymmetric()
    {
        $plaintext = 'my message';

        list($response, $output) = $this->runSample('encrypt_asymmetric', [
            self::$projectId,
            self::$locationId,
            self::$keyRingId,
            self::$asymmetricDecryptKeyId,
            "1",
            $plaintext
        ]);

        // PHP does not currently support custom MGF, so this sample is just a
        // comment.
        $this->assertTrue(true);
    }

    public function testEncryptSymmetric()
    {
        $plaintext = 'my message';

        list($response, $output) = $this->runSample('encrypt_symmetric', [
            self::$projectId,
            self::$locationId,
            self::$keyRingId,
            self::$symmetricKeyId,
            $plaintext
        ]);

        $this->assertContains('Ciphertext', $output);

        $client = new KeyManagementServiceClient();
        $keyName = $client->cryptoKeyName(self::$projectId, self::$locationId, self::$keyRingId, self::$symmetricKeyId);
        $response = $client->decrypt($keyName, $response->getCiphertext());
        $this->assertEquals($plaintext, $response->getPlaintext());
    }

    public function testGetKeyLabels()
    {
        list($key, $output) = $this->runSample('get_key_labels', [
            self::$projectId,
            self::$locationId,
            self::$keyRingId,
            self::$symmetricKeyId
        ]);

        $this->assertContains('foo = bar', $output);
        $this->assertEquals('bar', $key->getLabels()['foo']);
        $this->assertEquals('zap', $key->getLabels()['zip']);
    }

    public function testGetKeyVersionAttestation()
    {
        list($attestation, $output) = $this->runSample('get_key_version_attestation', [
            self::$projectId,
            self::$locationId,
            self::$keyRingId,
            self::$hsmKeyId,
            '1'
        ]);

        $this->assertContains('Got key attestation', $output);
        $this->assertNotNull($attestation->getContent());
    }

    public function testGetPublicKey()
    {
        list($key, $output) = $this->runSample('get_public_key', [
            self::$projectId,
            self::$locationId,
            self::$keyRingId,
            self::$asymmetricDecryptKeyId,
            '1'
        ]);

        $this->assertContains('Public key', $output);
        $this->assertNotNull($key);
        $this->assertNotNull($key->getPem());
    }

    public function testIamAddMember()
    {
        list($policy, $output) = $this->runSample('iam_add_member', [
            self::$projectId,
            self::$locationId,
            self::$keyRingId,
            self::$symmetricKeyId,
            'group:test@google.com'
        ]);

        $this->assertContains('Added group:test@google.com', $output);

        $binding = null;
        foreach ($policy->getBindings() as $b) {
            if ($b->getRole() === 'roles/cloudkms.cryptoKeyEncrypterDecrypter') {
                $binding = $b;
                break;
            }
        }
        $this->assertNotNull($binding);
        $this->assertContains('group:test@google.com', $binding->getMembers());
    }

    public function testIamGetPolicy()
    {
        list($policy, $output) = $this->runSample('iam_get_policy', [
            self::$projectId,
            self::$locationId,
            self::$keyRingId,
            self::$symmetricKeyId
        ]);

        $this->assertContains('IAM policy for', $output);
        $this->assertNotNull($policy);
    }

    public function testIamRemoveMember()
    {
        $client = new KeyManagementServiceClient();
        $keyName = $client->cryptoKeyName(self::$projectId, self::$locationId, self::$keyRingId, self::$asymmetricDecryptKeyId);

        $policy = $client->getIamPolicy($keyName);
        $bindings = $policy->getBindings();
        $bindings[] = (new Binding())
            ->setRole('roles/cloudkms.cryptoKeyEncrypterDecrypter')
            ->setMembers(['group:test@google.com', 'group:tester@google.com']);
        $policy->setBindings($bindings);
        $client->setIamPolicy($keyName, $policy);

        list($policy, $output) = $this->runSample('iam_remove_member', [
            self::$projectId,
            self::$locationId,
            self::$keyRingId,
            self::$asymmetricDecryptKeyId,
            'group:test@google.com'
        ]);

        $this->assertContains('Removed group:test@google.com', $output);

        $binding = null;
        foreach ($policy->getBindings() as $b) {
            if ($b->getRole() === 'roles/cloudkms.cryptoKeyEncrypterDecrypter') {
                $binding = $b;
                break;
            }
        }
        $this->assertNotNull($binding);
        $this->assertContains('group:tester@google.com', $binding->getMembers());
        $this->assertNotContains('group:test@google.com', $binding->getMembers());
    }

    public function testQuickstart()
    {
        list($keyRings, $output) = $this->runSample('quickstart', [
            self::$projectId,
            self::$locationId
        ]);

        $this->assertContains('Key rings in', $output);
        $this->assertNotEmpty($keyRings);
    }

    public function testSignAsymmetric()
    {
        $message = 'my message';

        list($signResponse, $output) = $this->runSample('sign_asymmetric', [
            self::$projectId,
            self::$locationId,
            self::$keyRingId,
            self::$asymmetricSignEcKeyId,
            '1',
            $message
        ]);

        $this->assertContains('Signature', $output);
        $this->assertNotEmpty($signResponse->getSignature());

        $client = new KeyManagementServiceClient();
        $keyVersionName = $client->cryptoKeyVersionName(self::$projectId, self::$locationId, self::$keyRingId, self::$asymmetricSignEcKeyId, '1');
        $publicKey = $client->getPublicKey($keyVersionName);
        $verified = openssl_verify($message, $signResponse->getSignature(), $publicKey->getPem(), OPENSSL_ALGO_SHA256);
        $this->assertEquals(1, $verified);
    }

    public function testUpdateKeyAddRotation()
    {
        list($key, $output) = $this->runSample('update_key_add_rotation', [
          self::$projectId,
          self::$locationId,
          self::$keyRingId,
          self::$symmetricKeyId
        ]);

        $this->assertContains('Updated key', $output);
        $this->assertEquals(2592000, $key->getRotationPeriod()->getSeconds());
    }

    public function testUpdateKeyRemoveLabels()
    {
        list($key, $output) = $this->runSample('update_key_remove_labels', [
          self::$projectId,
          self::$locationId,
          self::$keyRingId,
          self::$symmetricKeyId
        ]);

        $this->assertContains('Updated key', $output);
        $this->assertEmpty($key->getLabels());
    }

    public function testUpdateKeyRemoveRotation()
    {
        list($key, $output) = $this->runSample('update_key_remove_rotation', [
          self::$projectId,
          self::$locationId,
          self::$keyRingId,
          self::$symmetricKeyId
        ]);

        $this->assertContains('Updated key', $output);
        $this->assertEmpty($key->getRotationPeriod());
        $this->assertEmpty($key->getNextRotationTime());
    }

    public function testUpdateKeySetPrimary()
    {
        list($key, $output) = $this->runSample('update_key_set_primary', [
          self::$projectId,
          self::$locationId,
          self::$keyRingId,
          self::$symmetricKeyId,
          '1'
        ]);

        $this->assertContains('Updated primary', $output);
        $this->assertNotNull($key->getPrimary());
        $this->assertContains('1', $key->getPrimary()->getName());
    }

    public function testUpdateKeyUpdateLabels()
    {
        list($key, $output) = $this->runSample('update_key_update_labels', [
          self::$projectId,
          self::$locationId,
          self::$keyRingId,
          self::$symmetricKeyId
        ]);

        $this->assertContains('Updated key', $output);
        $this->assertNotNull($key->getLabels());
        $this->assertEquals('new_value', $key->getLabels()['new_label']);
    }

    public function testVerifyAsymmetricSignatureEc()
    {
        $message = 'my message';

        $client = new KeyManagementServiceClient();
        $keyVersionName = $client->cryptoKeyVersionName(self::$projectId, self::$locationId, self::$keyRingId, self::$asymmetricSignEcKeyId, '1');

        $digest = (new Digest())
            ->setSha256(hash('sha256', $message, true));

        $signResponse = $client->asymmetricSign($keyVersionName, $digest);

        list($verified, $output) = $this->runSample('verify_asymmetric_ec', [
          self::$projectId,
          self::$locationId,
          self::$keyRingId,
          self::$asymmetricSignEcKeyId,
          '1',
          $message,
          $signResponse->getSignature(),
        ]);

        $this->assertContains('Signature verified', $output);
        $this->assertTrue($verified);
    }

    public function testVerifyAsymmetricSignatureRsa()
    {
        $message = 'my message';
        list($verified, $output) = $this->runSample('verify_asymmetric_rsa', [
          self::$projectId,
          self::$locationId,
          self::$keyRingId,
          self::$asymmetricSignRsaKeyId,
          '1',
          $message,
          'signature...',
        ]);

        // PHP does not currently support custom MGF, so this sample is just a
        // comment.
        $this->assertTrue(true);
    }
}
