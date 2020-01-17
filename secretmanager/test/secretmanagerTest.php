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

namespace Google\Cloud\Samples\SecretManager;

use Google\Cloud\SecretManager\V1beta1\Replication;
use Google\Cloud\SecretManager\V1beta1\Replication\Automatic;
use Google\Cloud\SecretManager\V1beta1\Secret;
use Google\Cloud\SecretManager\V1beta1\SecretManagerServiceClient;
use Google\Cloud\SecretManager\V1beta1\SecretPayload;
use Google\Cloud\SecretManager\V1beta1\SecretVersion;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class secretmanagerTest extends TestCase
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

    public static function setUpBeforeClass()
    {
        self::$client = new SecretManagerServiceClient();

        self::$testSecret = self::createSecret();
        self::$testSecretToDelete = self::createSecret();
        self::$testSecretWithVersions = self::createSecret();
        self::$testSecretToCreateName = self::$client->secretName(self::$projectId, self::randomSecretId());

        self::$testSecretVersion = self::addSecretVersion(self::$testSecretWithVersions);
        self::$testSecretVersionToDestroy = self::addSecretVersion(self::$testSecretWithVersions);
        self::$testSecretVersionToDisable = self::addSecretVersion(self::$testSecretWithVersions);
        self::$testSecretVersionToEnable = self::addSecretVersion(self::$testSecretWithVersions);
        self::disableSecretVersion(self::$testSecretVersionToEnable);
    }

    public static function tearDownAfterClass()
    {
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
        $parent = self::$client->projectName(self::$projectId);
        $secretId = self::randomSecretId();

        return self::$client->createSecret($parent, $secretId, [
            'secret' => new Secret([
                'replication' => new Replication([
                    'automatic' => new Automatic(),
                ]),
            ]),
        ]);
    }

    private static function addSecretVersion(Secret $secret): SecretVersion
    {
        return self::$client->addSecretVersion($secret->getName(), new SecretPayload([
            'data' => 'my super secret data',
        ]));
    }

    private static function disableSecretVersion(SecretVersion $version): SecretVersion
    {
        return self::$client->disableSecretVersion($version->getName());
    }

    private static function deleteSecret(string $name)
    {
        try {
            self::$client->deleteSecret($name);
        } catch (\Google\ApiCore\ApiException $e) {
            if ($e->getStatus() != 'NOT_FOUND') {
                throw $e;
            }
        }
    }

    private static function explodeName(string $str): array
    {
        preg_match('/^projects\/(.+)(\/secrets\/(.+)(\/versions\/(.+))?)?$/U', $str, $matches);

        if (count($matches) > 5) {
            return [$matches[1], $matches[3], $matches[5]];
        } elseif (count($matches) > 3) {
            return [$matches[1], $matches[3]];
        } elseif (count($matches) > 1) {
            return [$matches[1]];
        }

        return [];
    }

    public function testAccessSecretVersion()
    {
        list($projectId, $secretId, $versionId) = self::explodeName(
            self::$testSecretVersion->getName()
        );

        $output = $this->runSnippet('access_secret_version', [
          $projectId,
          $secretId,
          $versionId,
        ]);

        $this->assertContains('my super secret data', $output);
    }

    public function testAddSecretVersion()
    {
        list($projectId, $secretId) = self::explodeName(
            self::$testSecretWithVersions->getName()
        );

        $output = $this->runSnippet('add_secret_version', [
            $projectId,
            $secretId,
        ]);

        $this->assertContains('Added secret version', $output);
    }

    public function testCreateSecret()
    {
        list($projectId, $secretId) = self::explodeName(
            self::$testSecretToCreateName
        );

        $output = $this->runSnippet('create_secret', [
            $projectId,
            $secretId,
        ]);

        $this->assertContains('Created secret', $output);
    }

    public function testDeleteSecret()
    {
        list($projectId, $secretId) = self::explodeName(
            self::$testSecretToDelete->getName()
        );

        $output = $this->runSnippet('delete_secret', [
            $projectId,
            $secretId,
        ]);

        $this->assertContains('Deleted secret', $output);
    }

    public function testDestroySecretVersion()
    {
        list($projectId, $secretId, $versionId) = self::explodeName(
            self::$testSecretVersionToDestroy->getName()
        );

        $output = $this->runSnippet('destroy_secret_version', [
            $projectId,
            $secretId,
            $versionId,
        ]);

        $this->assertContains('Destroyed secret version', $output);
    }

    public function testDisableSecretVersion()
    {
        list($projectId, $secretId, $versionId) = self::explodeName(
            self::$testSecretVersionToDisable->getName()
        );

        $output = $this->runSnippet('disable_secret_version', [
            $projectId,
            $secretId,
            $versionId,
        ]);

        $this->assertContains('Disabled secret version', $output);
    }

    public function testEnableSecretVersion()
    {
        list($projectId, $secretId, $versionId) = self::explodeName(
            self::$testSecretVersionToEnable->getName()
        );

        $output = $this->runSnippet('enable_secret_version', [
            $projectId,
            $secretId,
            $versionId,
        ]);

        $this->assertContains('Enabled secret version', $output);
    }

    public function testGetSecretVersion()
    {
        list($projectId, $secretId, $versionId) = self::explodeName(
            self::$testSecretVersion->getName()
        );

        $output = $this->runSnippet('get_secret_version', [
            $projectId,
            $secretId,
            $versionId,
        ]);

        $this->assertContains('Got secret version', $output);
        $this->assertContains('state ENABLED', $output);
    }

    public function testGetSecret()
    {
        list($projectId, $secretId) = self::explodeName(
            self::$testSecret->getName()
        );

        $output = $this->runSnippet('get_secret', [
            $projectId,
            $secretId,
        ]);

        $this->assertContains('secret', $output);
        $this->assertContains('replication policy AUTOMATIC', $output);
    }

    public function testListSecretVersions()
    {
        list($projectId, $secretId) = self::explodeName(
            self::$testSecretWithVersions->getName()
        );

        $output = $this->runSnippet('list_secret_versions', [
            $projectId,
            $secretId,
        ]);

        $this->assertContains('secret version', $output);
    }

    public function testListSecrets()
    {
        list($projectId, $secretId) = self::explodeName(
            self::$testSecret->getName()
        );

        $output = $this->runSnippet('list_secrets', [
            $projectId,
        ]);

        $this->assertContains('secret', $output);
        $this->assertContains($secretId, $output);
    }

    public function testUpdateSecret()
    {
        list($projectId, $secretId) = self::explodeName(
            self::$testSecret->getName()
        );

        $output = $this->runSnippet('update_secret', [
            $projectId,
            $secretId,
        ]);

        $this->assertContains('Updated secret', $output);
    }
}
