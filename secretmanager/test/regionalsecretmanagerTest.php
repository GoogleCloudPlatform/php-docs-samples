<?php
/*
 * Copyright 2024 Google LLC.
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

namespace Google\Cloud\Samples\SecretManager;

use Google\ApiCore\ApiException as GaxApiException;
use Google\Cloud\SecretManager\V1\AddSecretVersionRequest;
use Google\Cloud\SecretManager\V1\Client\SecretManagerServiceClient;
use Google\Cloud\SecretManager\V1\CreateSecretRequest;
use Google\Cloud\SecretManager\V1\DeleteSecretRequest;
use Google\Cloud\SecretManager\V1\DisableSecretVersionRequest;
use Google\Cloud\SecretManager\V1\Secret;
use Google\Cloud\SecretManager\V1\SecretPayload;
use Google\Cloud\SecretManager\V1\SecretVersion;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class regionalsecretmanagerTest extends TestCase
{
    use TestTrait;

    private static $client;
    private static $testSecret;
    private static $testSecretToDelete;
    private static $testSecretWithVersions;
    private static $testSecretToCreateName;
    private static $testSecretVersion;
    private static $testSecretVersionToDestroy;
    private static $testSecretVersionToDisable;
    private static $testSecretVersionToEnable;

    private static $iamUser = 'user:kapishsingh@google.com';
    private static $locationId = 'us-central1';

    public static function setUpBeforeClass(): void
    {
        $options = ['apiEndpoint' => 'secretmanager.' . self::$locationId . '.rep.googleapis.com' ];
        self::$client = new SecretManagerServiceClient($options);

        self::$testSecret = self::createSecret();
        self::$testSecretToDelete = self::createSecret();
        self::$testSecretWithVersions = self::createSecret();
        self::$testSecretToCreateName = self::$client->projectLocationSecretName(self::$projectId, self::$locationId, self::randomSecretId());
        self::$testSecretVersion = self::addSecretVersion(self::$testSecretWithVersions);
        self::$testSecretVersionToDestroy = self::addSecretVersion(self::$testSecretWithVersions);
        self::$testSecretVersionToDisable = self::addSecretVersion(self::$testSecretWithVersions);
        self::$testSecretVersionToEnable = self::addSecretVersion(self::$testSecretWithVersions);
        self::disableSecretVersion(self::$testSecretVersionToEnable);
    }

    public static function tearDownAfterClass(): void
    {
        $options = ['apiEndpoint' => 'secretmanager.' . self::$locationId . '.rep.googleapis.com' ];
        self::$client = new SecretManagerServiceClient($options);

        self::deleteSecret(self::$testSecret->getName());
        self::deleteSecret(self::$testSecretToDelete->getName());
        self::deleteSecret(self::$testSecretWithVersions->getName());
        self::deleteSecret(self::$testSecretToCreateName);
    }

    private static function randomSecretId(): string
    {
        return uniqid('php-snippets-');
    }

    private static function createSecret(): Secret
    {
        $parent = self::$client->locationName(self::$projectId, self::$locationId);
        $secretId = self::randomSecretId();
        $createSecretRequest = (new CreateSecretRequest())
            ->setParent($parent)
            ->setSecretId($secretId)
            ->setSecret(new Secret());

        return self::$client->createSecret($createSecretRequest);
    }

    private static function addSecretVersion(Secret $secret): SecretVersion
    {
        $addSecretVersionRequest = (new AddSecretVersionRequest())
            ->setParent($secret->getName())
            ->setPayload(new SecretPayload([
            'data' => 'my super secret data',
        ]));
        return self::$client->addSecretVersion($addSecretVersionRequest);
    }

    private static function disableSecretVersion(SecretVersion $version): SecretVersion
    {
        $disableSecretVersionRequest = (new DisableSecretVersionRequest())
            ->setName($version->getName());
        return self::$client->disableSecretVersion($disableSecretVersionRequest);
    }

    private static function deleteSecret(string $name)
    {
        try {
            $deleteSecretRequest = (new DeleteSecretRequest())
                ->setName($name);
            self::$client->deleteSecret($deleteSecretRequest);
        } catch (GaxApiException $e) {
            if ($e->getStatus() != 'NOT_FOUND') {
                throw $e;
            }
        }
    }

    public function testAccessSecretVersion()
    {
        $name = self::$client->parseName(self::$testSecretVersion->getName());

        $output = $this->runFunctionSnippet('access_regional_secret_version', [
            $name['project'],
            $name['location'],
            $name['secret'],
            $name['secret_version'],
        ]);

        $this->assertStringContainsString('my super secret data', $output);
    }

    public function testAddSecretVersion()
    {
        $name = self::$client->parseName(self::$testSecretWithVersions->getName());

        $output = $this->runFunctionSnippet('add_regional_secret_version', [
            $name['project'],
            $name['location'],
            $name['secret'],
        ]);

        $this->assertStringContainsString('Added secret version', $output);
    }

    public function testCreateSecret()
    {
        $name = self::$client->parseName(self::$testSecretToCreateName);

        $output = $this->runFunctionSnippet('create_regional_secret', [
            $name['project'],
            $name['location'],
            $name['secret'],
        ]);

        $this->assertStringContainsString('Created secret', $output);
    }

    public function testDeleteSecret()
    {
        $name = self::$client->parseName(self::$testSecretToDelete->getName());

        $output = $this->runFunctionSnippet('delete_regional_secret', [
            $name['project'],
            $name['location'],
            $name['secret'],
        ]);

        $this->assertStringContainsString('Deleted secret', $output);
    }

    public function testDestroySecretVersion()
    {
        $name = self::$client->parseName(self::$testSecretVersionToDestroy->getName());

        $output = $this->runFunctionSnippet('destroy_regional_secret_version', [
            $name['project'],
            $name['location'],
            $name['secret'],
            $name['secret_version'],
        ]);

        $this->assertStringContainsString('Destroyed secret version', $output);
    }

    public function testDisableSecretVersion()
    {
        $name = self::$client->parseName(self::$testSecretVersionToDisable->getName());

        $output = $this->runFunctionSnippet('disable_regional_secret_version', [
            $name['project'],
            $name['location'],
            $name['secret'],
            $name['secret_version'],
        ]);

        $this->assertStringContainsString('Disabled secret version', $output);
    }

    public function testEnableSecretVersion()
    {
        $name = self::$client->parseName(self::$testSecretVersionToEnable->getName());

        $output = $this->runFunctionSnippet('enable_regional_secret_version', [
            $name['project'],
            $name['location'],
            $name['secret'],
            $name['secret_version'],
        ]);

        $this->assertStringContainsString('Enabled secret version', $output);
    }

    public function testGetSecretVersion()
    {
        $name = self::$client->parseName(self::$testSecretVersion->getName());

        $output = $this->runFunctionSnippet('get_regional_secret_version', [
            $name['project'],
            $name['location'],
            $name['secret'],
            $name['secret_version'],
        ]);

        $this->assertStringContainsString('Got secret version', $output);
        $this->assertStringContainsString('state ENABLED', $output);
    }

    public function testGetSecret()
    {
        $name = self::$client->parseName(self::$testSecret->getName());

        $output = $this->runFunctionSnippet('get_regional_secret', [
            $name['project'],
            $name['location'],
            $name['secret'],
        ]);

        $this->assertStringContainsString('secret', $output);
    }

    public function testIamGrantAccess()
    {
        $name = self::$client->parseName(self::$testSecret->getName());

        $output = $this->runFunctionSnippet('regional_iam_grant_access', [
            $name['project'],
            $name['location'],
            $name['secret'],
            self::$iamUser,
        ]);

        $this->assertStringContainsString('Updated IAM policy', $output);
    }

    public function testIamRevokeAccess()
    {
        $name = self::$client->parseName(self::$testSecret->getName());

        $output = $this->runFunctionSnippet('regional_iam_revoke_access', [
            $name['project'],
            $name['location'],
            $name['secret'],
            self::$iamUser,
        ]);

        $this->assertStringContainsString('Updated IAM policy', $output);
    }

    public function testListSecretVersions()
    {
        $name = self::$client->parseName(self::$testSecretWithVersions->getName());

        $output = $this->runFunctionSnippet('list_regional_secret_versions', [
            $name['project'],
            $name['location'],
            $name['secret'],
        ]);

        $this->assertStringContainsString('secret version', $output);
    }

    public function testListSecrets()
    {
        $name = self::$client->parseName(self::$testSecret->getName());

        $output = $this->runFunctionSnippet('list_regional_secrets', [
            $name['project'],
            $name['location'],
        ]);

        $this->assertStringContainsString('secret', $output);
        $this->assertStringContainsString($name['secret'], $output);
    }

    public function testUpdateSecret()
    {
        $name = self::$client->parseName(self::$testSecret->getName());

        $output = $this->runFunctionSnippet('update_regional_secret', [
            $name['project'],
            $name['location'],
            $name['secret'],
        ]);

        $this->assertStringContainsString('Updated secret', $output);
    }

    public function testUpdateSecretWithAlias()
    {
        $name = self::$client->parseName(self::$testSecretWithVersions->getName());

        $output = $this->runFunctionSnippet('update_regional_secret_with_alias', [
            $name['project'],
            $name['location'],
            $name['secret'],
        ]);

        $this->assertStringContainsString('Updated secret', $output);
    }
}
