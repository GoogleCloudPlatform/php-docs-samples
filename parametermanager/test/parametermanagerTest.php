<?php
/*
 * Copyright 2025 Google LLC.
 *
 * Licensed under the Apache License, Version 2.0 (the 'License');
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an 'AS IS' BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Google\Cloud\Samples\ParameterManager;

use Exception;
use Google\ApiCore\ApiException;
use Google\Cloud\Kms\V1\CreateCryptoKeyRequest;
use Google\Cloud\Kms\V1\CreateKeyRingRequest;
use Google\Cloud\Kms\V1\CryptoKey;
use Google\Cloud\Kms\V1\CryptoKey\CryptoKeyPurpose;
use Google\Cloud\Kms\V1\CryptoKeyVersion\CryptoKeyVersionAlgorithm;
use Google\Cloud\Kms\V1\CryptoKeyVersion\CryptoKeyVersionState;
use Google\Cloud\Kms\V1\CryptoKeyVersionTemplate;
use Google\Cloud\Kms\V1\DestroyCryptoKeyVersionRequest;
use Google\Cloud\Kms\V1\GetCryptoKeyVersionRequest;
use Google\Cloud\Kms\V1\KeyRing;
use Google\Cloud\Kms\V1\ListCryptoKeysRequest;
use Google\Cloud\Kms\V1\ListCryptoKeyVersionsRequest;
use Google\Cloud\Kms\V1\ProtectionLevel;
use Google\Cloud\TestUtils\TestTrait;
use Google\ApiCore\ApiException as GaxApiException;
use Google\Cloud\Kms\V1\Client\KeyManagementServiceClient;
use PHPUnit\Framework\TestCase;
use Google\Cloud\ParameterManager\V1\Client\ParameterManagerClient;
use Google\Cloud\ParameterManager\V1\DeleteParameterRequest;

class parametermanagerTest extends TestCase
{
    use TestTrait;

    public const JSON_PAYLOAD = '{"username": "test-user", "host": "localhost"}';
    private static $kmsClient;
    private static $client;
    private static $locationId = 'global';
    private static $keyRingId;
    private static $cryptoKey;
    private static $cryptoUpdatedKey;
    private static $testParameterNameWithKms;

    public static function setUpBeforeClass(): void
    {
        self::$client = new ParameterManagerClient();
        self::$kmsClient = new KeyManagementServiceClient();

        self::$testParameterNameWithKms = self::$client->parameterName(self::$projectId, self::$locationId, self::randomId());

        self::$keyRingId = self::createKeyRing();
        $hsmKey = self::randomId();
        self::createHsmKey($hsmKey);

        $hsmUdpatedKey = self::randomId();
        self::createUpdatedHsmKey($hsmUdpatedKey);
    }

    public static function tearDownAfterClass(): void
    {
        $keyRingName = self::$kmsClient->keyRingName(self::$projectId, self::$locationId, self::$keyRingId);
        $listCryptoKeysRequest = (new ListCryptoKeysRequest())
            ->setParent($keyRingName);
        $keys = self::$kmsClient->listCryptoKeys($listCryptoKeysRequest);
        foreach ($keys as $key) {
            $listCryptoKeyVersionsRequest = (new ListCryptoKeyVersionsRequest())
                ->setParent($key->getName())
                ->setFilter('state != DESTROYED AND state != DESTROY_SCHEDULED');

            $versions = self::$kmsClient->listCryptoKeyVersions($listCryptoKeyVersionsRequest);
            foreach ($versions as $version) {
                $destroyCryptoKeyVersionRequest = (new DestroyCryptoKeyVersionRequest())
                    ->setName($version->getName());
                self::$kmsClient->destroyCryptoKeyVersion($destroyCryptoKeyVersionRequest);
            }
        }

        self::deleteParameter(self::$testParameterNameWithKms);
    }

    private static function deleteParameter(string $name)
    {
        try {
            $deleteParameterRequest = (new DeleteParameterRequest())
                ->setName($name);
            self::$client->deleteParameter($deleteParameterRequest);
        } catch (GaxApiException $e) {
            if ($e->getStatus() != 'NOT_FOUND') {
                throw $e;
            }
        }
    }

    private static function randomId(): string
    {
        return uniqid('php-snippets-');
    }

    private static function createKeyRing()
    {
        $id = 'test-pm-snippets';
        $locationName = self::$kmsClient->locationName(self::$projectId, self::$locationId);
        $keyRing = new KeyRing();
        try {
            $createKeyRingRequest = (new CreateKeyRingRequest())
                ->setParent($locationName)
                ->setKeyRingId($id)
                ->setKeyRing($keyRing);
            $keyRing = self::$kmsClient->createKeyRing($createKeyRingRequest);
            return $keyRing->getName();
        } catch (ApiException $e) {
            if ($e->getStatus() == 'ALREADY_EXISTS') {
                return $id;
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    private static function createHsmKey(string $id)
    {
        $keyRingName = self::$kmsClient->keyRingName(self::$projectId, self::$locationId, self::$keyRingId);
        $key = (new CryptoKey())
            ->setPurpose(CryptoKeyPurpose::ENCRYPT_DECRYPT)
            ->setVersionTemplate((new CryptoKeyVersionTemplate)
                ->setProtectionLevel(ProtectionLevel::HSM)
                ->setAlgorithm(CryptoKeyVersionAlgorithm::GOOGLE_SYMMETRIC_ENCRYPTION))
            ->setLabels(['foo' => 'bar', 'zip' => 'zap']);
        $createCryptoKeyRequest = (new CreateCryptoKeyRequest())
            ->setParent($keyRingName)
            ->setCryptoKeyId($id)
            ->setCryptoKey($key);
        $cryptoKey = self::$kmsClient->createCryptoKey($createCryptoKeyRequest);
        self::$cryptoKey = $cryptoKey->getName();
        return self::waitForReady($cryptoKey);
    }

    private static function createUpdatedHsmKey(string $id)
    {
        $keyRingName = self::$kmsClient->keyRingName(self::$projectId, self::$locationId, self::$keyRingId);
        $key = (new CryptoKey())
            ->setPurpose(CryptoKeyPurpose::ENCRYPT_DECRYPT)
            ->setVersionTemplate((new CryptoKeyVersionTemplate)
                ->setProtectionLevel(ProtectionLevel::HSM)
                ->setAlgorithm(CryptoKeyVersionAlgorithm::GOOGLE_SYMMETRIC_ENCRYPTION))
            ->setLabels(['foo' => 'bar', 'zip' => 'zap']);
        $createCryptoKeyRequest = (new CreateCryptoKeyRequest())
            ->setParent($keyRingName)
            ->setCryptoKeyId($id)
            ->setCryptoKey($key);
        $cryptoKey = self::$kmsClient->createCryptoKey($createCryptoKeyRequest);
        self::$cryptoUpdatedKey = $cryptoKey->getName();
        return self::waitForReady($cryptoKey);
    }

    private static function waitForReady(CryptoKey $key)
    {
        $versionName = $key->getName() . '/cryptoKeyVersions/1';
        $getCryptoKeyVersionRequest = (new GetCryptoKeyVersionRequest())
            ->setName($versionName);
        $version = self::$kmsClient->getCryptoKeyVersion($getCryptoKeyVersionRequest);
        $attempts = 0;
        while ($version->getState() != CryptoKeyVersionState::ENABLED) {
            if ($attempts > 10) {
                $msg = sprintf('key version %s was not ready after 10 attempts', $versionName);
                throw new \Exception($msg);
            }
            usleep(500);
            $getCryptoKeyVersionRequest = (new GetCryptoKeyVersionRequest())
                ->setName($versionName);
            $version = self::$kmsClient->getCryptoKeyVersion($getCryptoKeyVersionRequest);
            $attempts += 1;
        }
        return $key;
    }

    public function testCreateParamWithKmsKey()
    {
        $name = self::$client->parseName(self::$testParameterNameWithKms);

        $output = $this->runFunctionSnippet('create_param_with_kms_key', [
            $name['project'],
            $name['parameter'],
            self::$cryptoKey,
        ]);

        $this->assertStringContainsString('Created parameter', $output);
        $this->assertStringContainsString('with kms key ' . self::$cryptoKey, $output);
    }

    public function testUpdateParamKmsKey()
    {
        $name = self::$client->parseName(self::$testParameterNameWithKms);

        $output = $this->runFunctionSnippet('update_param_kms_key', [
            $name['project'],
            $name['parameter'],
            self::$cryptoUpdatedKey,
        ]);

        $this->assertStringContainsString('Updated parameter ', $output);
        $this->assertStringContainsString('with kms key ' . self::$cryptoUpdatedKey, $output);
    }

    public function testRemoveParamKmsKey()
    {
        $name = self::$client->parseName(self::$testParameterNameWithKms);

        $output = $this->runFunctionSnippet('remove_param_kms_key', [
            $name['project'],
            $name['parameter'],
        ]);

        $this->assertStringContainsString('Removed kms key for parameter ', $output);
    }

}
