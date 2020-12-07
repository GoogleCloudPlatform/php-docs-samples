<?php
/*
 * Copyright 2020 Google LLC.
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
use Google\Cloud\SecretManager\V1\Replication;
use Google\Cloud\SecretManager\V1\Replication\Automatic;
use Google\Cloud\SecretManager\V1\Secret;
use Google\Cloud\SecretManager\V1\SecretManagerServiceClient;
use Google\Cloud\SecretManager\V1\SecretPayload;
use Google\Cloud\SecretManager\V1\SecretVersion;
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

    private static $iamUser = 'user:sethvargo@google.com';

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

        return self::$client->createSecret($parent, $secretId,
            new Secret([
                'replication' => new Replication([
                    'automatic' => new Automatic(),
                ]),
            ])
        );
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
        } catch (GaxApiException $e) {
            if ($e->getStatus() != 'NOT_FOUND') {
                throw $e;
            }
        }
    }

    public function testAccessSecretVersion()
    {
        $name = self::$client->parseName(self::$testSecretVersion->getName());

        $output = $this->runSnippet('access_secret_version', [
            $name['project'],
            $name['secret'],
            $name['secret_version'],
        ]);

        $this->assertContains('my super secret data', $output);
    }

    public function testAddSecretVersion()
    {
        $name = self::$client->parseName(self::$testSecretWithVersions->getName());

        $output = $this->runSnippet('add_secret_version', [
            $name['project'],
            $name['secret'],
        ]);

        $this->assertContains('Added secret version', $output);
    }

    public function testCreateSecret()
    {
        $name = self::$client->parseName(self::$testSecretToCreateName);

        $output = $this->runSnippet('create_secret', [
            $name['project'],
            $name['secret'],
        ]);

        $this->assertContains('Created secret', $output);
    }

    public function testDeleteSecret()
    {
        $name = self::$client->parseName(self::$testSecretToDelete->getName());

        $output = $this->runSnippet('delete_secret', [
            $name['project'],
            $name['secret'],
        ]);

        $this->assertContains('Deleted secret', $output);
    }

    public function testDestroySecretVersion()
    {
        $name = self::$client->parseName(self::$testSecretVersionToDestroy->getName());

        $output = $this->runSnippet('destroy_secret_version', [
            $name['project'],
            $name['secret'],
            $name['secret_version'],
        ]);

        $this->assertContains('Destroyed secret version', $output);
    }

    public function testDisableSecretVersion()
    {
        $name = self::$client->parseName(self::$testSecretVersionToDisable->getName());

        $output = $this->runSnippet('disable_secret_version', [
            $name['project'],
            $name['secret'],
            $name['secret_version'],
        ]);

        $this->assertContains('Disabled secret version', $output);
    }

    public function testEnableSecretVersion()
    {
        $name = self::$client->parseName(self::$testSecretVersionToEnable->getName());

        $output = $this->runSnippet('enable_secret_version', [
            $name['project'],
            $name['secret'],
            $name['secret_version'],
        ]);

        $this->assertContains('Enabled secret version', $output);
    }

    public function testGetSecretVersion()
    {
        $name = self::$client->parseName(self::$testSecretVersion->getName());

        $output = $this->runSnippet('get_secret_version', [
            $name['project'],
            $name['secret'],
            $name['secret_version'],
        ]);

        $this->assertContains('Got secret version', $output);
        $this->assertContains('state ENABLED', $output);
    }

    public function testGetSecret()
    {
        $name = self::$client->parseName(self::$testSecret->getName());

        $output = $this->runSnippet('get_secret', [
            $name['project'],
            $name['secret'],
        ]);

        $this->assertContains('secret', $output);
        $this->assertContains('replication policy AUTOMATIC', $output);
    }

    public function testIamGrantAccess()
    {
        $name = self::$client->parseName(self::$testSecret->getName());

        $output = $this->runSnippet('iam_grant_access', [
            $name['project'],
            $name['secret'],
            self::$iamUser,
        ]);

        $this->assertContains('Updated IAM policy', $output);
    }

    public function testIamRevokeAccess()
    {
        $name = self::$client->parseName(self::$testSecret->getName());

        $output = $this->runSnippet('iam_revoke_access', [
            $name['project'],
            $name['secret'],
            self::$iamUser,
        ]);

        $this->assertContains('Updated IAM policy', $output);
    }

    public function testListSecretVersions()
    {
        $name = self::$client->parseName(self::$testSecretWithVersions->getName());

        $output = $this->runSnippet('list_secret_versions', [
            $name['project'],
            $name['secret'],
        ]);

        $this->assertContains('secret version', $output);
    }

    public function testListSecrets()
    {
        $name = self::$client->parseName(self::$testSecret->getName());

        $output = $this->runSnippet('list_secrets', [
            $name['project'],
        ]);

        $this->assertContains('secret', $output);
        $this->assertContains($name['secret'], $output);
    }

    public function testUpdateSecret()
    {
        $name = self::$client->parseName(self::$testSecret->getName());

        $output = $this->runSnippet('update_secret', [
            $name['project'],
            $name['secret'],
        ]);

        $this->assertContains('Updated secret', $output);
    }
}
