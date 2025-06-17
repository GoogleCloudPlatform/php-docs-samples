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

use Google\Cloud\TestUtils\TestTrait;
use Google\ApiCore\ApiException as GaxApiException;
use Google\Cloud\SecretManager\V1\AddSecretVersionRequest;
use Google\Cloud\SecretManager\V1\Client\SecretManagerServiceClient;
use Google\Cloud\SecretManager\V1\CreateSecretRequest;
use Google\Cloud\SecretManager\V1\DeleteSecretRequest;
use PHPUnit\Framework\TestCase;
use Google\Cloud\SecretManager\V1\Secret;
use Google\Cloud\SecretManager\V1\SecretVersion;
use Google\Cloud\SecretManager\V1\Replication;
use Google\Cloud\SecretManager\V1\Replication\Automatic;
use Google\Cloud\SecretManager\V1\SecretPayload;
use Google\Cloud\ParameterManager\V1\Client\ParameterManagerClient;
use Google\Cloud\ParameterManager\V1\CreateParameterRequest;
use Google\Cloud\ParameterManager\V1\CreateParameterVersionRequest;
use Google\Cloud\ParameterManager\V1\DeleteParameterRequest;
use Google\Cloud\ParameterManager\V1\DeleteParameterVersionRequest;
use Google\Cloud\ParameterManager\V1\Parameter;
use Google\Cloud\ParameterManager\V1\ParameterFormat;
use Google\Cloud\ParameterManager\V1\ParameterVersion;
use Google\Cloud\ParameterManager\V1\ParameterVersionPayload;
use Google\Cloud\Iam\V1\Binding;
use Google\Cloud\Iam\V1\GetIamPolicyRequest;
use Google\Cloud\Iam\V1\SetIamPolicyRequest;

class parametermanagerTest extends TestCase
{
    use TestTrait;

    public const PAYLOAD = 'test123';
    public const JSON_PAYLOAD = '{"username": "test-user", "host": "localhost"}';
    public const SECRET_ID = 'projects/project-id/secrets/secret-id/versions/latest';

    private static $secretClient;
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

    public static function setUpBeforeClass(): void
    {
        self::$secretClient = new SecretManagerServiceClient();
        self::$client = new ParameterManagerClient();

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
    }

    public static function tearDownAfterClass(): void
    {
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
}
