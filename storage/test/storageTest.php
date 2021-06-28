<?php
/**
 * Copyright 2018 Google Inc.
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


namespace Google\Cloud\Samples\Storage;

use Google\Auth\CredentialsLoader;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\Core\Exception\NotFoundException;
use Google\Cloud\Core\Exception\BadRequestException;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for storage commands.
 */
class storageTest extends TestCase
{
    use TestTrait;

    private static $bucketName;
    private static $storage;
    private static $tempBucket;

    public static function setUpBeforeClass(): void
    {
        self::checkProjectEnvVars();
        self::$bucketName = self::requireEnv('GOOGLE_STORAGE_BUCKET');
        self::$storage = new StorageClient();
        self::$tempBucket = self::$storage->createBucket(
            sprintf('%s-test-bucket-%s', self::$projectId, time())
        );
    }

    public static function tearDownAfterClass(): void
    {
        self::$tempBucket->delete();
    }

    public function testBucketAcl()
    {
        $output = $this->runFunctionSnippet('get_bucket_acl', [
            self::$tempBucket->name(),
        ]);

        $this->assertRegExp("/: OWNER/", $output);
    }

    public function testManageBucketAcl()
    {
        $jsonKey = CredentialsLoader::fromEnv();
        $acl = self::$tempBucket->acl();
        $entity = sprintf('user-%s', $jsonKey['client_email']);
        $bucketUrl = sprintf('gs://%s', self::$tempBucket->name());

        $output = $this->runFunctionSnippet('add_bucket_acl', [
            self::$tempBucket->name(),
            $entity,
            'READER'
        ]);

        $expected = "Added $entity (READER) to $bucketUrl ACL\n";
        $this->assertEquals($expected, $output);

        $aclInfo = $acl->get(['entity' => $entity]);
        $this->assertArrayHasKey('role', $aclInfo);
        $this->assertEquals('READER', $aclInfo['role']);

        $output = $this->runFunctionSnippet('get_bucket_acl_for_entity', [
            self::$tempBucket->name(),
            $entity,
        ]);

        $expected = "$entity: READER\n";
        $this->assertEquals($expected, $output);

        $output = $this->runFunctionSnippet('delete_bucket_acl', [
            self::$tempBucket->name(),
            $entity,
        ]);

        $expected = "Deleted $entity from $bucketUrl ACL\n";
        $this->assertEquals($expected, $output);

        try {
            $acl->get(['entity' => $entity]);
            $this->fail();
        } catch (NotFoundException $e) {
            $this->assertTrue(true);
        }
    }

    public function testListBuckets()
    {
        $output = $this->runFunctionSnippet('list_buckets');

        $this->assertStringContainsString("Bucket:", $output);
    }

    public function testCreateGetDeleteBuckets()
    {
        $bucketName = sprintf('test-bucket-%s-%s', time(), rand());
        $bucket = self::$storage->bucket($bucketName);

        $this->assertFalse($bucket->exists());

        $this->runFunctionSnippet('create_bucket', [$bucketName]);

        $bucket->reload();
        $this->assertTrue($bucket->exists());

        $output = $this->runFunctionSnippet('get_bucket_metadata', [$bucketName]);

        $this->assertStringContainsString("Bucket Metadata:", $output);

        $output = $this->runFunctionSnippet('delete_bucket', [$bucketName]);

        $this->assertFalse($bucket->exists());

        $this->assertStringContainsString("Bucket deleted: $bucketName", $output);
    }

    public function testBucketDefaultAcl()
    {
        $output = $this->runFunctionSnippet('get_bucket_default_acl', [
            self::$tempBucket->name(),
        ]);

        $this->assertStringContainsString(": OWNER", $output);
    }

    public function testManageBucketDefaultAcl()
    {
        $bucketName = self::$tempBucket->name();
        $acl = self::$tempBucket->defaultAcl();

        $output = $this->runFunctionSnippet('add_bucket_default_acl', [
            $bucketName,
            'allAuthenticatedUsers',
            'READER',
        ]);

        $aclInfo = $acl->get(['entity' => 'allAuthenticatedUsers']);
        $this->assertArrayHasKey('role', $aclInfo);
        $this->assertEquals('READER', $aclInfo['role']);

        $output .= $this->runFunctionSnippet('get_bucket_default_acl_for_entity', [
            $bucketName,
            'allAuthenticatedUsers',
        ]);

        $output .= $this->runFunctionSnippet('delete_bucket_default_acl', [
            $bucketName,
            'allAuthenticatedUsers'
        ]);

        try {
            $acl->get(['entity' => 'allAuthenticatedUsers']);
            $this->fail();
        } catch (NotFoundException $e) {
            $this->assertTrue(true);
        }

        $bucketUrl = sprintf('gs://%s', $bucketName);
        $outputString = <<<EOF
Added allAuthenticatedUsers (READER) to $bucketUrl default ACL
allAuthenticatedUsers: READER
Deleted allAuthenticatedUsers from $bucketUrl default ACL

EOF;
        $this->assertEquals($outputString, $output);
    }

    public function testManageBucketLabels()
    {
        $label1 = 'label1-' . time();
        $label2 = 'label2-' . time();
        $value1 = 'value1-' . time();
        $value2 = 'value2-' . time();
        $value3 = 'value3-' . time();

        $output = $this->runFunctionSnippet('add_bucket_label', [
            self::$bucketName,
            $label1,
            $value1
        ]);

        $this->assertEquals(sprintf(
            'Added label %s (%s) to %s' . PHP_EOL,
            $label1,
            $value1,
            self::$bucketName
        ), $output);

        $output = $this->runFunctionSnippet('get_bucket_labels', [
            self::$bucketName
        ]);

        $this->assertStringContainsString(sprintf('%s: value1', $label1), $output);

        $output = $this->runFunctionSnippet('add_bucket_label', [
            self::$bucketName,
            $label2,
            $value2,
        ]);

        $this->assertEquals(sprintf(
            'Added label %s (%s) to %s' . PHP_EOL,
            $label2,
            $value2,
            self::$bucketName
        ), $output);

        $output = $this->runFunctionSnippet('get_bucket_labels', [
            self::$bucketName
        ]);

        $this->assertStringContainsString(sprintf('%s: %s', $label1, $value1), $output);
        $this->assertStringContainsString(sprintf('%s: %s', $label2, $value2), $output);

        $output = $this->runFunctionSnippet('add_bucket_label', [
            self::$bucketName,
            $label1,
            $value3
        ]);

        $this->assertEquals(sprintf(
            'Added label %s (%s) to %s' . PHP_EOL,
            $label1,
            $value3,
            self::$bucketName
        ), $output);

        $output = $this->runFunctionSnippet('get_bucket_labels', [
            self::$bucketName
        ]);

        $this->assertStringContainsString(sprintf('%s: %s', $label1, $value3), $output);
        $this->assertStringNotContainsString($value1, $output);

        $output = $this->runFunctionSnippet('remove_bucket_label', [
            self::$bucketName,
            $label1,
        ]);

        $this->assertEquals(sprintf(
            'Removed label %s from %s' . PHP_EOL,
            $label1,
            self::$bucketName
        ), $output);

        $output = $this->runFunctionSnippet('remove_bucket_label', [
            self::$bucketName,
            $label2,
        ]);

        $this->assertEquals(sprintf(
            'Removed label %s from %s' . PHP_EOL,
            $label2,
            self::$bucketName
        ), $output);

        $output = $this->runFunctionSnippet('get_bucket_labels', [
            self::$bucketName
        ]);

        $this->assertStringNotContainsString($label1, $output);
        $this->assertStringNotContainsString($label2, $output);
    }

    public function testGenerateEncryptionKey()
    {
        $output = $this->runFunctionSnippet('generate_encryption_key');

        $this->assertStringContainsString("Your encryption key:", $output);
    }

    public function testEncryptedFile()
    {
        $objectName = $this->requireEnv('GOOGLE_STORAGE_OBJECT');
        $objectName .= '.encrypted';
        $key = base64_encode(random_bytes(32));
        $uploadFrom = tempnam(sys_get_temp_dir(), '/tests');
        $uploadFromBasename = basename($uploadFrom);
        file_put_contents($uploadFrom, $contents = 'foo' . rand());
        $downloadTo = tempnam(sys_get_temp_dir(), '/tests');
        $downloadToBasename = basename($downloadTo);

        $output = $this->runFunctionSnippet('upload_encrypted_object', [
            self::$bucketName,
            $objectName,
            $uploadFrom,
            $key,
        ]);

        $output .= $this->runFunctionSnippet('download_encrypted_object', [
            self::$bucketName,
            $objectName,
            $downloadTo,
            $key,
        ]);

        $this->assertTrue(file_exists($downloadTo));
        $this->assertEquals($contents, file_get_contents($downloadTo));

        $objectUrl = sprintf('gs://%s/%s', self::$bucketName, $objectName);
        $outputString = <<<EOF
Uploaded encrypted $uploadFromBasename to $objectUrl
Encrypted object $objectUrl downloaded to $downloadToBasename

EOF;
        $this->assertEquals($outputString, $output);
    }

    public function testRotateEncryptionKey()
    {
        $objectName = $this->requireEnv('GOOGLE_STORAGE_OBJECT') . '.encrypted';
        $key = base64_encode(random_bytes(32));
        $newKey = base64_encode(random_bytes(32));
        $uploadFrom = tempnam(sys_get_temp_dir(), '/tests');
        $uploadFromBasename = basename($uploadFrom);
        file_put_contents($uploadFrom, $contents = 'foo' . rand());
        $downloadTo = tempnam(sys_get_temp_dir(), '/tests');
        $downloadToBasename = basename($downloadTo);

        $output = $this->runFunctionSnippet('upload_encrypted_object', [
            self::$bucketName,
            $objectName,
            $uploadFrom,
            $key,
        ]);

        $output .= $this->runFunctionSnippet('rotate_encryption_key', [
            self::$bucketName,
            $objectName,
            $key,
            $newKey,
        ]);

        $output .= $this->runFunctionSnippet('download_encrypted_object', [
            self::$bucketName,
            $objectName,
            $downloadTo,
            $newKey,
        ]);

        $this->assertTrue(file_exists($downloadTo));
        $this->assertEquals($contents, file_get_contents($downloadTo));

        $objectUrl = sprintf('gs://%s/%s', self::$bucketName, $objectName);
        $outputString = <<<EOF
Uploaded encrypted $uploadFromBasename to $objectUrl
Rotated encryption key for object $objectUrl
Encrypted object $objectUrl downloaded to $downloadToBasename

EOF;
        $this->assertEquals($outputString, $output);
    }

    public function testDownloadEncryptedFileFails()
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('The provided encryption key is incorrect');

        $objectName = $this->requireEnv('GOOGLE_STORAGE_OBJECT') . '.encrypted';
        $invalidKey = base64_encode(random_bytes(32));
        $downloadTo = tempnam(sys_get_temp_dir(), '/tests');

        $output = $this->runFunctionSnippet('download_encrypted_object', [
            self::$bucketName,
            $objectName,
            $downloadTo,
            $invalidKey,
        ]);
    }

    public function testEnableDefaultKmsKey()
    {
        $kmsEncryptedBucketName = self::$bucketName . '-kms-encrypted';

        $output = $this->runFunctionSnippet('enable_default_kms_key', [
            self::$projectId,
            $kmsEncryptedBucketName,
            $this->keyName(),
        ]);

        $this->assertEquals($output, sprintf(
            'Default KMS key for %s was set to %s' . PHP_EOL,
            $kmsEncryptedBucketName,
            $this->keyName()
        ));
    }

    /** @depends testEnableDefaultKmsKey */
    public function testUploadWithKmsKey()
    {
        $kmsEncryptedBucketName = self::$bucketName . '-kms-encrypted';

        $objectName = 'test-object-' . time();
        $uploadFrom = tempnam(sys_get_temp_dir(), '/tests');
        file_put_contents($uploadFrom, 'foo' . rand());

        $output = $this->runFunctionSnippet('upload_with_kms_key', [
            self::$projectId,
            $kmsEncryptedBucketName,
            $objectName,
            $uploadFrom,
            $this->keyName(),
        ]);

        $this->assertEquals($output, sprintf(
            'Uploaded %s to gs://%s/%s using encryption key %s' . PHP_EOL,
            basename($uploadFrom),
            $kmsEncryptedBucketName,
            $objectName,
            $this->keyName()
        ));
    }

    private function keyName()
    {
        return sprintf(
            'projects/%s/locations/us/keyRings/%s/cryptoKeys/%s',
            self::$projectId,
            $this->requireEnv('GOOGLE_STORAGE_KMS_KEYRING'),
            $this->requireEnv('GOOGLE_STORAGE_KMS_CRYPTOKEY')
        );
    }
}
