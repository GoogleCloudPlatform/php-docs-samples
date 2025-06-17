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
use Google\ApiCore\ApiException as GaxApiException;
use Google\Cloud\Iam\V1\Binding;
use Google\Cloud\Iam\V1\GetIamPolicyRequest;
use Google\Cloud\Iam\V1\SetIamPolicyRequest;
use Google\Cloud\Kms\V1\Client\KeyManagementServiceClient;
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
use Google\Cloud\ParameterManager\V1\Client\ParameterManagerClient;
use Google\Cloud\ParameterManager\V1\CreateParameterRequest;
use Google\Cloud\ParameterManager\V1\CreateParameterVersionRequest;
use Google\Cloud\ParameterManager\V1\DeleteParameterRequest;
use Google\Cloud\ParameterManager\V1\DeleteParameterVersionRequest;
use Google\Cloud\ParameterManager\V1\Parameter;
use Google\Cloud\ParameterManager\V1\ParameterFormat;
use Google\Cloud\ParameterManager\V1\ParameterVersion;
use Google\Cloud\ParameterManager\V1\ParameterVersionPayload;
use Google\Cloud\SecretManager\V1\AddSecretVersionRequest;
use Google\Cloud\SecretManager\V1\Client\SecretManagerServiceClient;
use Google\Cloud\SecretManager\V1\CreateSecretRequest;
use Google\Cloud\SecretManager\V1\DeleteSecretRequest;
use Google\Cloud\SecretManager\V1\Replication;
use Google\Cloud\SecretManager\V1\Replication\Automatic;
use Google\Cloud\SecretManager\V1\Secret;
use Google\Cloud\SecretManager\V1\SecretPayload;
use Google\Cloud\SecretManager\V1\SecretVersion;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class parametermanagerTest extends TestCase
{
    use TestTrait;

    public const PAYLOAD = 'test123';
    public const JSON_PAYLOAD = '{"username": "test-user", "host": "localhost"}';
    public const SECRET_ID = 'projects/project-id/secrets/secret-id/versions/latest';

    private static $secretClient;
    private static $kmsClient;
    private static $client;
    private static $locationId = 'global';

    private static $testParameterName;
    private static $testParameterNameWithFormat;

    private static $testParameterForVersion;
    private static $testParameterVersionName;

    private static $testParameterForVersionWithFormat;
    private static $testParameterVersionNameWithFormat;
    private static $testParameterVersionNameWithSecretReference;

    private static $testParameterToGet;
    private static $testParameterVersionToGet;
    private static $testParameterVersionToGet1;

    private static $testParameterToRender;
    private static $testParameterVersionToRender;
    private static $testSecret;

    private static $testParameterToDelete;
    private static $testParameterToDeleteVersion;
    private static $testParameterVersionToDelete;

    private static $keyRingId;
    private static $cryptoKey;
    private static $cryptoUpdatedKey;
    private static $testParameterNameWithKms;

    public static function setUpBeforeClass(): void
    {
        self::$secretClient = new SecretManagerServiceClient();
        self::$client = new ParameterManagerClient();
        self::$kmsClient = new KeyManagementServiceClient();

        self::$testParameterName = self::$client->parameterName(self::$projectId, self::$locationId, self::randomId());
        self::$testParameterNameWithFormat = self::$client->parameterName(self::$projectId, self::$locationId, self::randomId());

        $testParameterId = self::randomId();
        self::$testParameterForVersion = self::createParameter($testParameterId, ParameterFormat::UNFORMATTED);
        self::$testParameterVersionName = self::$client->parameterVersionName(self::$projectId, self::$locationId, $testParameterId, self::randomId());

        $testParameterId = self::randomId();
        self::$testParameterForVersionWithFormat = self::createParameter($testParameterId, ParameterFormat::JSON);
        self::$testParameterVersionNameWithFormat = self::$client->parameterVersionName(self::$projectId, self::$locationId, $testParameterId, self::randomId());
        self::$testParameterVersionNameWithSecretReference = self::$client->parameterVersionName(self::$projectId, self::$locationId, $testParameterId, self::randomId());

        $testParameterId = self::randomId();
        self::$testParameterToGet = self::createParameter($testParameterId, ParameterFormat::UNFORMATTED);
        self::$testParameterVersionToGet = self::createParameterVersion($testParameterId, self::randomId(), self::PAYLOAD);
        self::$testParameterVersionToGet1 = self::createParameterVersion($testParameterId, self::randomId(), self::PAYLOAD);

        $testParameterId = self::randomId();
        self::$testParameterToRender = self::createParameter($testParameterId, ParameterFormat::JSON);
        self::$testSecret = self::createSecret(self::randomId());
        self::addSecretVersion(self::$testSecret);
        $payload = sprintf('{"username": "test-user", "password": "__REF__(//secretmanager.googleapis.com/%s/versions/latest)"}', self::$testSecret->getName());
        self::$testParameterVersionToRender = self::createParameterVersion($testParameterId, self::randomId(), $payload);
        self::iamGrantAccess(self::$testSecret->getName(), self::$testParameterToRender->getPolicyMember()->getIamPolicyUidPrincipal());

        self::$testParameterToDelete = self::createParameter(self::randomId(), ParameterFormat::JSON);
        $testParameterId = self::randomId();
        self::$testParameterToDeleteVersion = self::createParameter($testParameterId, ParameterFormat::JSON);
        self::$testParameterVersionToDelete = self::createParameterVersion($testParameterId, self::randomId(), self::JSON_PAYLOAD);

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
        self::deleteParameter(self::$testParameterName);
        self::deleteParameter(self::$testParameterNameWithFormat);

        self::deleteParameterVersion(self::$testParameterVersionName);
        self::deleteParameter(self::$testParameterForVersion->getName());

        self::deleteParameterVersion(self::$testParameterVersionNameWithFormat);
        self::deleteParameterVersion(self::$testParameterVersionNameWithSecretReference);
        self::deleteParameter(self::$testParameterForVersionWithFormat->getName());

        self::deleteParameterVersion(self::$testParameterVersionToGet->getName());
        self::deleteParameterVersion(self::$testParameterVersionToGet1->getName());
        self::deleteParameter(self::$testParameterToGet->getName());

        self::deleteParameterVersion(self::$testParameterVersionToRender->getName());
        self::deleteParameter(self::$testParameterToRender->getName());
        self::deleteSecret(self::$testSecret->getName());

        self::deleteParameterVersion(self::$testParameterVersionToDelete->getName());
        self::deleteParameter(self::$testParameterToDeleteVersion->getName());
        self::deleteParameter(self::$testParameterToDelete->getName());
    }

    private static function randomId(): string
    {
        return uniqid('php-snippets-');
    }

    private static function createParameter(string $parameterId, int $format): Parameter
    {
        $parent = self::$client->locationName(self::$projectId, self::$locationId);
        $parameter = (new Parameter())
            ->setFormat($format);

        $request = (new CreateParameterRequest())
            ->setParent($parent)
            ->setParameterId($parameterId)
            ->setParameter($parameter);

        return self::$client->createParameter($request);
    }

    private static function createParameterVersion(string $parameterId, string $versionId, string $payload): ParameterVersion
    {
        $parent = self::$client->parameterName(self::$projectId, self::$locationId, $parameterId);

        $parameterVersionPayload = new ParameterVersionPayload();
        $parameterVersionPayload->setData($payload);

        $parameterVersion = new ParameterVersion();
        $parameterVersion->setPayload($parameterVersionPayload);

        $request = (new CreateParameterVersionRequest())
            ->setParent($parent)
            ->setParameterVersionId($versionId)
            ->setParameterVersion($parameterVersion);

        return self::$client->createParameterVersion($request);
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

    private static function deleteParameterVersion(string $name)
    {
        try {
            $deleteParameterVersionRequest = (new DeleteParameterVersionRequest())
                ->setName($name);
            self::$client->deleteParameterVersion($deleteParameterVersionRequest);
        } catch (GaxApiException $e) {
            if ($e->getStatus() != 'NOT_FOUND') {
                throw $e;
            }
        }
    }

    private static function createSecret(string $secretId): Secret
    {
        $parent = self::$secretClient->projectName(self::$projectId);
        $createSecretRequest = (new CreateSecretRequest())
            ->setParent($parent)
            ->setSecretId($secretId)
            ->setSecret(new Secret([
                'replication' => new Replication([
                    'automatic' => new Automatic(),
                ]),
            ]));

        return self::$secretClient->createSecret($createSecretRequest);
    }

    private static function addSecretVersion(Secret $secret): SecretVersion
    {
        $addSecretVersionRequest = (new AddSecretVersionRequest())
            ->setParent($secret->getName())
            ->setPayload(new SecretPayload([
                'data' => self::PAYLOAD,
            ]));
        return self::$secretClient->addSecretVersion($addSecretVersionRequest);
    }

    private static function deleteSecret(string $name)
    {
        try {
            $deleteSecretRequest = (new DeleteSecretRequest())
                ->setName($name);
            self::$secretClient->deleteSecret($deleteSecretRequest);
        } catch (GaxApiException $e) {
            if ($e->getStatus() != 'NOT_FOUND') {
                throw $e;
            }
        }
    }

    private static function iamGrantAccess(string $secretName, string $member)
    {
        $policy = self::$secretClient->getIamPolicy((new GetIamPolicyRequest())->setResource($secretName));

        $bindings = $policy->getBindings();
        $bindings[] = new Binding([
            'members' => [$member],
            'role' => 'roles/secretmanager.secretAccessor',
        ]);

        $policy->setBindings($bindings);
        $request = (new SetIamPolicyRequest())
            ->setResource($secretName)
            ->setPolicy($policy);
        self::$secretClient->setIamPolicy($request);
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

    public function testCreateParam()
    {
        $name = self::$client->parseName(self::$testParameterName);

        $output = $this->runFunctionSnippet('create_param', [
            $name['project'],
            $name['parameter'],
        ]);

        $this->assertStringContainsString('Created parameter', $output);
    }

    public function testCreateStructuredParameter()
    {
        $name = self::$client->parseName(self::$testParameterNameWithFormat);

        $output = $this->runFunctionSnippet('create_structured_param', [
            $name['project'],
            $name['parameter'],
            'JSON',
        ]);

        $this->assertStringContainsString('Created parameter', $output);
    }

    public function testCreateParamVersion()
    {
        $name = self::$client->parseName(self::$testParameterVersionName);

        $output = $this->runFunctionSnippet('create_param_version', [
            $name['project'],
            $name['parameter'],
            $name['parameter_version'],
            self::PAYLOAD,
        ]);

        $this->assertStringContainsString('Created parameter version', $output);
    }

    public function testCreateStructuredParamVersion()
    {
        $name = self::$client->parseName(self::$testParameterVersionNameWithFormat);

        $output = $this->runFunctionSnippet('create_structured_param_version', [
            $name['project'],
            $name['parameter'],
            $name['parameter_version'],
            self::JSON_PAYLOAD,
        ]);

        $this->assertStringContainsString('Created parameter version', $output);
    }

    public function testCreateParamVersionWithSecret()
    {
        $name = self::$client->parseName(self::$testParameterVersionNameWithSecretReference);

        $output = $this->runFunctionSnippet('create_param_version_with_secret', [
            $name['project'],
            $name['parameter'],
            $name['parameter_version'],
            self::SECRET_ID,
        ]);

        $this->assertStringContainsString('Created parameter version', $output);
    }

    public function testGetParam()
    {
        $name = self::$client->parseName(self::$testParameterToGet->getName());

        $output = $this->runFunctionSnippet('get_param', [
            $name['project'],
            $name['parameter'],
        ]);

        $this->assertStringContainsString('Found parameter', $output);
    }

    public function testGetParamVersion()
    {
        $name = self::$client->parseName(self::$testParameterVersionToGet->getName());

        $output = $this->runFunctionSnippet('get_param_version', [
            $name['project'],
            $name['parameter'],
            $name['parameter_version'],
        ]);

        $this->assertStringContainsString('Found parameter version', $output);
        $this->assertStringContainsString('Payload', $output);
    }

    public function testListParam()
    {
        $output = $this->runFunctionSnippet('list_params', [
            self::$projectId,
        ]);

        $this->assertStringContainsString('Found parameter', $output);
    }

    public function testListParamVersion()
    {
        $name = self::$client->parseName(self::$testParameterToGet->getName());

        $output = $this->runFunctionSnippet('list_param_versions', [
            $name['project'],
            $name['parameter'],
        ]);

        $this->assertStringContainsString('Found parameter version', $output);
    }

    public function testRenderParamVersion()
    {
        $name = self::$client->parseName(self::$testParameterVersionToRender->getName());

        $output = $this->runFunctionSnippet('render_param_version', [
            $name['project'],
            $name['parameter'],
            $name['parameter_version'],
        ]);

        $this->assertStringContainsString('Rendered parameter version payload', $output);
    }

    public function testDisableParamVersion()
    {
        $name = self::$client->parseName(self::$testParameterVersionToGet->getName());

        $output = $this->runFunctionSnippet('disable_param_version', [
            $name['project'],
            $name['parameter'],
            $name['parameter_version'],
        ]);

        $this->assertStringContainsString('Disabled parameter version', $output);
    }

    public function testEnableParamVersion()
    {
        $name = self::$client->parseName(self::$testParameterVersionToGet->getName());

        $output = $this->runFunctionSnippet('enable_param_version', [
            $name['project'],
            $name['parameter'],
            $name['parameter_version'],
        ]);

        $this->assertStringContainsString('Enabled parameter version', $output);
    }

    public function testDeleteParam()
    {
        $name = self::$client->parseName(self::$testParameterToDelete->getName());

        $output = $this->runFunctionSnippet('delete_param', [
            $name['project'],
            $name['parameter'],
        ]);

        $this->assertStringContainsString('Deleted parameter', $output);
    }

    public function testDeleteParamVersion()
    {
        $name = self::$client->parseName(self::$testParameterVersionToDelete->getName());

        $output = $this->runFunctionSnippet('delete_param_version', [
            $name['project'],
            $name['parameter'],
            $name['parameter_version'],
        ]);

        $this->assertStringContainsString('Deleted parameter version', $output);
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
