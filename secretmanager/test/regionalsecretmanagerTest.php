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
use Google\Cloud\ResourceManager\V3\DeleteTagKeyRequest;
use Google\Cloud\ResourceManager\V3\DeleteTagValueRequest;
use Google\Cloud\ResourceManager\V3\Client\TagKeysClient;
use Google\Cloud\ResourceManager\V3\CreateTagKeyRequest;
use Google\Cloud\ResourceManager\V3\TagKey;
use Google\Cloud\ResourceManager\V3\Client\TagValuesClient;
use Google\Cloud\ResourceManager\V3\CreateTagValueRequest;
use Google\Cloud\ResourceManager\V3\TagValue;
use Google\Cloud\SecretManager\V1\AddSecretVersionRequest;
use Google\Cloud\SecretManager\V1\Client\SecretManagerServiceClient;
use Google\Cloud\SecretManager\V1\CreateSecretRequest;
use Google\Cloud\SecretManager\V1\DeleteSecretRequest;
use Google\Cloud\SecretManager\V1\DisableSecretVersionRequest;
use Google\Cloud\SecretManager\V1\GetSecretRequest;
use Google\Cloud\SecretManager\V1\Secret;
use Google\Cloud\SecretManager\V1\SecretPayload;
use Google\Cloud\SecretManager\V1\SecretVersion;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

class regionalsecretmanagerTest extends TestCase
{
    use TestTrait;

    private static $client;
    private static $tagKeyClient;
    private static $tagValuesClient;

    private static $testSecret;
    private static $testSecretToDelete;
    private static $testSecretWithVersions;
    private static $testSecretToCreateName;
    private static $testSecretVersion;
    private static $testSecretVersionToDestroy;
    private static $testSecretVersionToDisable;
    private static $testSecretVersionToEnable;
    private static $testSecretVersionToDestroyWithETag;
    private static $testSecretVersionToDisableWithETag;
    private static $testSecretVersionToEnableWithETag;
    private static $testSecretWithTagToCreateName;
    private static $testSecretBindTagToCreateName;
    private static $testSecretWithLabelsToCreateName;
    private static $testSecretWithAnnotationsToCreateName;
    private static $testSecretWithDelayedDestroyToCreateName;
    private static $testSecretWithExpirationToCreateName;
    private static $testSecretWithCMEKToCreateName;
    private static $testSecretWithTopicToCreateName;

    private static $iamUser = 'user:kapishsingh@google.com';
    private static $locationId = 'us-central1';
    private static $testLabelKey = 'test-label-key';
    private static $testLabelValue = 'test-label-value';
    private static $testUpdatedLabelValue = 'test-label-new-value';
    private static $testAnnotationKey = 'test-annotation-key';
    private static $testAnnotationValue = 'test-annotation-value';
    private static $testUpdatedAnnotationValue = 'test-annotation-new-value';
    private static $testDelayedDestroyTime = 86400;

    private static $testTagKey;
    private static $testTagValue;

    private static $skipRotationTests = false;
    private static $testRotationTopic;

    public static function setUpBeforeClass(): void
    {
        $options = ['apiEndpoint' => 'secretmanager.' . self::$locationId . '.rep.googleapis.com' ];
        self::$client = new SecretManagerServiceClient($options);
        self::$tagKeyClient = new TagKeysClient();
        self::$tagValuesClient = new TagValuesClient();

        self::$testSecret = self::createSecret();
        self::$testSecretToDelete = self::createSecret();
        self::$testSecretWithVersions = self::createSecret();
        self::$testSecretToCreateName = self::$client->projectLocationSecretName(self::$projectId, self::$locationId, self::randomSecretId());
        self::$testSecretWithTagToCreateName = self::$client->projectLocationSecretName(self::$projectId, self::$locationId, self::randomSecretId());
        self::$testSecretBindTagToCreateName = self::$client->projectLocationSecretName(self::$projectId, self::$locationId, self::randomSecretId());
        self::$testSecretWithLabelsToCreateName = self::$client->projectLocationSecretName(self::$projectId, self::$locationId, self::randomSecretId());
        self::$testSecretWithAnnotationsToCreateName = self::$client->projectLocationSecretName(self::$projectId, self::$locationId, self::randomSecretId());
        self::$testSecretWithDelayedDestroyToCreateName = self::$client->projectLocationSecretName(self::$projectId, self::$locationId, self::randomSecretId());
        self::$testSecretWithExpirationToCreateName = self::$client->projectLocationSecretName(self::$projectId, self::$locationId, self::randomSecretId());
        self::$testSecretWithCMEKToCreateName = self::$client->projectLocationSecretName(self::$projectId, self::$locationId, self::randomSecretId());
        self::$testSecretWithTopicToCreateName = self::$client->projectLocationSecretName(self::$projectId, self::$locationId, self::randomSecretId());

        self::$testSecretVersion = self::addSecretVersion(self::$testSecretWithVersions);
        self::$testSecretVersionToDestroy = self::addSecretVersion(self::$testSecretWithVersions);
        self::$testSecretVersionToDisable = self::addSecretVersion(self::$testSecretWithVersions);
        self::$testSecretVersionToEnable = self::addSecretVersion(self::$testSecretWithVersions);
        self::disableSecretVersion(self::$testSecretVersionToEnable);
        self::$testSecretVersionToDestroyWithETag = self::addSecretVersion(self::$testSecretWithVersions);
        self::$testSecretVersionToDisableWithETag = self::addSecretVersion(self::$testSecretWithVersions);
        self::$testSecretVersionToEnableWithETag = self::addSecretVersion(self::$testSecretWithVersions);
        self::disableSecretVersion(self::$testSecretVersionToEnableWithETag);

        self::$testTagKey = self::createTagKey(self::randomSecretId());
        self::$testTagValue = self::createTagValue(self::randomSecretId());

        // GOOGLE_CLOUD_PUBSUB_TOPIC (projects/{project}/topics/{topic}).
        $envTopic = getenv('GOOGLE_CLOUD_PUBSUB_TOPIC');
        if ($envTopic === false || $envTopic === '') {
            self::$skipRotationTests = true;
            printf('Skipping tests dependent on GOOGLE_CLOUD_PUBSUB_TOPIC as it is not set.%s', PHP_EOL);
        } else {
            self::$testRotationTopic = $envTopic;
        }
    }

    public static function tearDownAfterClass(): void
    {
        $options = ['apiEndpoint' => 'secretmanager.' . self::$locationId . '.rep.googleapis.com' ];
        self::$client = new SecretManagerServiceClient($options);

        self::deleteSecret(self::$testSecret->getName());
        self::deleteSecret(self::$testSecretToDelete->getName());
        self::deleteSecret(self::$testSecretWithVersions->getName());
        self::deleteSecret(self::$testSecretToCreateName);
        self::deleteSecret(self::$testSecretWithTagToCreateName);
        self::deleteSecret(self::$testSecretBindTagToCreateName);
        self::deleteSecret(self::$testSecretWithLabelsToCreateName);
        self::deleteSecret(self::$testSecretWithAnnotationsToCreateName);
        self::deleteSecret(self::$testSecretWithDelayedDestroyToCreateName);
        self::deleteSecret(self::$testSecretWithExpirationToCreateName);
        self::deleteSecret(self::$testSecretWithCMEKToCreateName);
        self::deleteSecret(self::$testSecretWithTopicToCreateName);
        sleep(15); // Added a sleep to wait for the tag unbinding
        self::deleteTagValue();
        self::deleteTagKey();
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

    private static function getSecret(string $projectId, string $locationId, string $secretId): Secret
    {
        $name = self::$client->projectLocationSecretName($projectId, $locationId, $secretId);
        $getSecretRequest = (new GetSecretRequest())->setName($name);
        return self::$client->getSecret($getSecretRequest);
    }

    private static function createTagKey(string $short_name): string
    {
        $parent = self::$client->projectName(self::$projectId);
        $tagKey = (new TagKey())
            ->setParent($parent)
            ->setShortName($short_name);

        $request = (new CreateTagKeyRequest())
            ->setTagKey($tagKey);

        $operation = self::$tagKeyClient->createTagKey($request);
        $operation->pollUntilComplete();

        if ($operation->operationSucceeded()) {
            $createdTagKey = $operation->getResult();
            printf("Tag key created: %s\n", $createdTagKey->getName());
            return $createdTagKey->getName();
        } else {
            $error = $operation->getError();
            printf("Error creating tag key: %s\n", $error->getMessage());
            return '';
        }
    }

    private static function createTagValue(string $short_name): string
    {
        $tagValuesClient = new TagValuesClient();
        $tagValue = (new TagValue())
            ->setParent(self::$testTagKey)
            ->setShortName($short_name);

        $request = (new CreateTagValueRequest())
            ->setTagValue($tagValue);

        $operation = self::$tagValuesClient->createTagValue($request);
        $operation->pollUntilComplete();

        if ($operation->operationSucceeded()) {
            $createdTagValue = $operation->getResult();
            printf("Tag value created: %s\n", $createdTagValue->getName());
            return $createdTagValue->getName();
        } else {
            $error = $operation->getError();
            printf("Error creating tag value: %s\n", $error->getMessage());
            return '';
        }
    }

    private static function deleteTagKey()
    {
        $request = (new DeleteTagKeyRequest())
            ->setName(self::$testTagKey);

        $operation = self::$tagKeyClient->deleteTagKey($request);
        $operation->pollUntilComplete();

        if ($operation->operationSucceeded()) {
            printf("Tag key deleted: %s\n", self::$testTagValue);
        } else {
            $error = $operation->getError();
            printf("Error deleting tag key: %s\n", $error->getMessage());
        }
    }

    private static function deleteTagValue()
    {
        $request = (new DeleteTagValueRequest())
            ->setName(self::$testTagValue);

        $operation = self::$tagValuesClient->deleteTagValue($request);
        $operation->pollUntilComplete();

        if ($operation->operationSucceeded()) {
            printf("Tag value deleted: %s\n", self::$testTagValue);
        } else {
            $error = $operation->getError();
            printf("Error deleting tag value: %s\n", $error->getMessage());
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

    public function testDeleteSecretUsingEtag()
    {
        $secret = self::createSecret();
        $name = self::$client->parseName($secret->getName());

        $output = $this->runFunctionSnippet('delete_regional_secret_using_etag', [
            $name['project'],
            $name['location'],
            $name['secret'],
        ]);

        $this->assertStringContainsString('Deleted secret', $output);
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

    public function testDestroySecretVersionUsingEtag()
    {
        $name = self::$client->parseName(self::$testSecretVersionToDestroyWithETag->getName());

        $output = $this->runFunctionSnippet('destroy_regional_secret_version_using_etag', [
            $name['project'],
            $name['location'],
            $name['secret'],
            $name['secret_version'],
        ]);

        $this->assertStringContainsString('Destroyed secret version', $output);
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

    public function testDisableSecretVersionUsingEtag()
    {
        $name = self::$client->parseName(self::$testSecretVersionToDisableWithETag->getName());

        $output = $this->runFunctionSnippet('disable_regional_secret_version_using_etag', [
            $name['project'],
            $name['location'],
            $name['secret'],
            $name['secret_version'],
        ]);

        $this->assertStringContainsString('Disabled secret version', $output);
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

    public function testEnableSecretVersionUsingEtag()
    {
        $name = self::$client->parseName(self::$testSecretVersionToEnableWithETag->getName());

        $output = $this->runFunctionSnippet('enable_regional_secret_version_using_etag', [
            $name['project'],
            $name['location'],
            $name['secret'],
            $name['secret_version'],
        ]);

        $this->assertStringContainsString('Enabled secret version', $output);
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

    public function testListSecretVersionsWithFilter()
    {
        $name = self::$client->parseName(self::$testSecretWithVersions->getName());

        // Filter for enabled versions.
        $filter = 'state = ENABLED';

        $output = $this->runFunctionSnippet('list_regional_secret_versions_with_filter', [
            $name['project'],
            $name['location'],
            $name['secret'],
            $filter,
        ]);

        $this->assertStringContainsString('Found secret version', $output);
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

    public function testListSecretsWithFilter()
    {
        $name = self::$client->parseName(self::$testSecret->getName());

        $filter = 'name:' . $name['secret'];

        $output = $this->runFunctionSnippet('list_regional_secrets_with_filter', [
            $name['project'],
            $name['location'],
            $filter,
        ]);

        $this->assertStringContainsString('Found secret', $output);
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

    public function testUpdateSecretUsingEtag()
    {
        $name = self::$client->parseName(self::$testSecret->getName());

        $output = $this->runFunctionSnippet('update_regional_secret_using_etag', [
            $name['project'],
            $name['location'],
            $name['secret'],
            'etaglabel',
            'etagvalue',
        ]);

        $this->assertStringContainsString('Updated secret', $output);
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

    public function testCreateSecretWithTags()
    {
        $name = self::$client->parseName(self::$testSecretWithTagToCreateName);

        $output = $this->runFunctionSnippet('create_regional_secret_with_tags', [
            $name['project'],
            $name['location'],
            $name['secret'],
            self::$testTagKey,
            self::$testTagValue
        ]);

        $this->assertStringContainsString('Created secret', $output);
    }

    public function testBindTagsToSecret()
    {
        $name = self::$client->parseName(self::$testSecretBindTagToCreateName);

        $output = $this->runFunctionSnippet('bind_tags_to_regional_secret', [
            $name['project'],
            $name['location'],
            $name['secret'],
            self::$testTagValue
        ]);

        $this->assertStringContainsString('Created secret', $output);
        $this->assertStringContainsString('Tag binding created for secret', $output);
    }

    public function testCreateSecretWithLabels()
    {
        $name = self::$client->parseName(self::$testSecretWithLabelsToCreateName);

        $output = $this->runFunctionSnippet('create_regional_secret_with_labels', [
            $name['project'],
            $name['location'],
            $name['secret'],
            self::$testLabelKey,
            self::$testLabelValue
        ]);

        $this->assertStringContainsString('Created secret', $output);
    }

    public function testCreateSecretWithAnnotations()
    {
        $name = self::$client->parseName(self::$testSecretWithAnnotationsToCreateName);

        $output = $this->runFunctionSnippet('create_regional_secret_with_annotations', [
            $name['project'],
            $name['location'],
            $name['secret'],
            self::$testAnnotationKey,
            self::$testAnnotationValue
        ]);

        $this->assertStringContainsString('Created secret', $output);
    }

    public function testViewSecretAnnotations()
    {
        $name = self::$client->parseName(self::$testSecretWithAnnotationsToCreateName);

        $output = $this->runFunctionSnippet('view_regional_secret_annotations', [
            $name['project'],
            $name['location'],
            $name['secret']
        ]);

        $this->assertStringContainsString('Get secret', $output);
    }

    public function testViewSecretLabels()
    {
        $name = self::$client->parseName(self::$testSecretWithLabelsToCreateName);

        $output = $this->runFunctionSnippet('view_regional_secret_labels', [
            $name['project'],
            $name['location'],
            $name['secret']
        ]);

        $this->assertStringContainsString('Get secret', $output);
    }

    public function testEditSecretLabels()
    {
        $name = self::$client->parseName(self::$testSecretWithLabelsToCreateName);

        $output = $this->runFunctionSnippet('edit_regional_secret_labels', [
            $name['project'],
            $name['location'],
            $name['secret'],
            self::$testLabelKey,
            self::$testUpdatedLabelValue
        ]);

        $this->assertStringContainsString('Updated secret', $output);
    }

    public function testEditSecretAnnotations()
    {
        $name = self::$client->parseName(self::$testSecretWithAnnotationsToCreateName);

        $output = $this->runFunctionSnippet('edit_regional_secret_annotations', [
            $name['project'],
            $name['location'],
            $name['secret'],
            self::$testAnnotationKey,
            self::$testUpdatedAnnotationValue
        ]);

        $this->assertStringContainsString('Updated secret', $output);
    }

    public function testDeleteSecretLabel()
    {
        $name = self::$client->parseName(self::$testSecretWithLabelsToCreateName);

        $output = $this->runFunctionSnippet('delete_regional_secret_label', [
            $name['project'],
            $name['location'],
            $name['secret'],
            self::$testLabelKey
        ]);

        $this->assertStringContainsString('Updated secret', $output);
    }

    public function testDeleteSecretAnnotation()
    {
        $name = self::$client->parseName(self::$testSecretWithAnnotationsToCreateName);

        $output = $this->runFunctionSnippet('delete_regional_secret_annotation', [
            $name['project'],
            $name['location'],
            $name['secret'],
            self::$testAnnotationKey
        ]);

        $this->assertStringContainsString('Updated secret', $output);
    }

    public function testCreateSecretWithDelayedDestroyed()
    {
        $name = self::$client->parseName(self::$testSecretWithDelayedDestroyToCreateName);

        $output = $this->runFunctionSnippet('create_regional_secret_with_delayed_destroy', [
            $name['project'],
            $name['location'],
            $name['secret'],
            self::$testDelayedDestroyTime
        ]);

        $this->assertStringContainsString('Created secret', $output);

        $secret = self::getSecret($name['project'], $name['location'], $name['secret']);
        $this->assertEquals(self::$testDelayedDestroyTime, $secret->getVersionDestroyTtl()->getSeconds());
    }

    public function testDisableSecretDelayedDestroy()
    {
        $name = self::$client->parseName(self::$testSecretWithDelayedDestroyToCreateName);

        $output = $this->runFunctionSnippet('disable_regional_secret_delayed_destroy', [
            $name['project'],
            $name['location'],
            $name['secret']
        ]);

        $this->assertStringContainsString('Updated secret', $output);

        $secret = self::getSecret($name['project'], $name['location'], $name['secret']);
        $this->assertNull($secret->getVersionDestroyTtl());
    }

    public function testUpdateSecretWithDelayedDestroyed()
    {
        $name = self::$client->parseName(self::$testSecretWithDelayedDestroyToCreateName);

        $output = $this->runFunctionSnippet('update_regional_secret_with_delayed_destroy', [
            $name['project'],
            $name['location'],
            $name['secret'],
            self::$testDelayedDestroyTime
        ]);

        $this->assertStringContainsString('Updated secret', $output);
        $secret = self::getSecret($name['project'], $name['location'], $name['secret']);
        $this->assertEquals(self::$testDelayedDestroyTime, $secret->getVersionDestroyTtl()->getSeconds());
    }

    public function testCreateSecretWithExpiration()
    {
        $name = self::$client->parseName(self::$testSecretWithExpirationToCreateName);

        $output = $this->runFunctionSnippet('create_regional_secret_with_expiration', [
            $name['project'],
            $name['location'],
            $name['secret'],
        ]);

        $this->assertStringContainsString('Created secret', $output);
    }

    public function testUpdateSecretWithExpiration()
    {
        $name = self::$client->parseName(self::$testSecretWithExpirationToCreateName);

        $output = $this->runFunctionSnippet('update_regional_secret_with_expiration', [
            $name['project'],
            $name['location'],
            $name['secret'],
        ]);

        $this->assertStringContainsString('Updated secret', $output);
    }

    public function testDeleteSecretExpiration()
    {
        $name = self::$client->parseName(self::$testSecretWithExpirationToCreateName);

        $output = $this->runFunctionSnippet('delete_regional_secret_expiration', [
            $name['project'],
            $name['location'],
            $name['secret'],
        ]);

        $this->assertStringContainsString('Updated secret', $output);
    }

    public function testCreateSecretWithCmek()
    {
        $kmsKey = getenv('GOOGLE_CLOUD_REGIONAL_KMS_KEY');
        if ($kmsKey === false || $kmsKey === '') {
            $this->markTestSkipped('GOOGLE_CLOUD_REGIONAL_KMS_KEY not set');
            printf('Skipping testCreateSecretWithTopic dependent on GOOGLE_CLOUD_REGIONAL_KMS_KEY%s', PHP_EOL);
        }

        $name = self::$client->parseName(self::$testSecretWithCMEKToCreateName);

        $output = $this->runFunctionSnippet('create_regional_secret_with_cmek', [
            $name['project'],
            $name['location'],
            $name['secret'],
            $kmsKey,
        ]);

        $this->assertStringContainsString('Created secret', $output);
    }

    public function testCreateSecretWithTopic()
    {
        if (self::$skipRotationTests) {
            $this->markTestSkipped('GOOGLE_CLOUD_PUBSUB_TOPIC not set');
            printf('Skipping testCreateSecretWithTopic dependent on GOOGLE_CLOUD_PUBSUB_TOPIC%s', PHP_EOL);
        }

        $name = self::$client->parseName(self::$testSecretWithTopicToCreateName);

        $output = $this->runFunctionSnippet('create_regional_secret_with_topic', [
            $name['project'],
            $name['location'],
            $name['secret'],
            self::$testRotationTopic,
        ]);

        $this->assertStringContainsString('Created secret', $output);
    }
}
