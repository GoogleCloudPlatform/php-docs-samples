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

use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use Google\Cloud\Core\Exception\NotFoundException;
use Google\Cloud\Core\Exception\BadRequestException;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for storage commands.
 */
class storageTest extends TestCase
{
    use TestTrait;
    use ExecuteCommandTrait;

    private static $commandFile = __DIR__ . '/../storage.php';
    private static $bucketName;
    private static $storage;
    private static $tempBucket;

    public static function setUpBeforeClass()
    {
        self::checkProjectEnvVars();
        self::$bucketName = self::requireEnv('GOOGLE_STORAGE_BUCKET');
        self::$storage = new StorageClient();
        self::$tempBucket = self::$storage->createBucket(
            sprintf('%s-test-bucket-%s', self::$projectId, time())
        );
    }

    public static function tearDownAfterClass()
    {
        self::$tempBucket->delete();
    }

    public function testBucketAcl()
    {
        $output = $this->runCommand('bucket-acl', [
            'bucket' => self::$tempBucket->name(),
        ]);

        $this->assertRegExp("/: OWNER/", $output);
    }

    public function testManageBucketAcl()
    {
        $acl = self::$tempBucket->acl();
        $bucketUrl = sprintf('gs://%s', self::$tempBucket->name());

        $output = $this->runCommand('bucket-acl', [
            'bucket' => self::$tempBucket->name(),
            '--entity' => 'allAuthenticatedUsers',
            '--create' => true,
        ]);

        $expected = "Added allAuthenticatedUsers (READER) to $bucketUrl ACL\n";
        $this->assertEquals($expected, $output);

        $aclInfo = $acl->get(['entity' => 'allAuthenticatedUsers']);
        $this->assertArrayHasKey('role', $aclInfo);
        $this->assertEquals('READER', $aclInfo['role']);

        $output = $this->runCommand('bucket-acl', [
            'bucket' => self::$tempBucket->name(),
            '--entity' => 'allAuthenticatedUsers',
        ]);

        $expected = "allAuthenticatedUsers: READER\n";
        $this->assertEquals($expected, $output);

        $output = $this->runCommand('bucket-acl', [
            'bucket' => self::$tempBucket->name(),
            '--entity' => 'allAuthenticatedUsers',
            '--delete' => true,
        ]);

        $expected = "Deleted allAuthenticatedUsers from $bucketUrl ACL\n";
        $this->assertEquals($expected, $output);

        try {
            $acl->get(['entity' => 'allAuthenticatedUsers']);
            $this->fail();
        } catch (NotFoundException $e) {
            $this->assertTrue(true);
        }
    }

    public function testListBuckets()
    {
        $output = $this->runCommand('buckets');

        $this->assertContains("Bucket:", $output);
    }

    public function testCreateGetDeleteBuckets()
    {
        $bucketName = sprintf('test-bucket-%s-%s', time(), rand());
        $bucket = self::$storage->bucket($bucketName);

        $this->assertFalse($bucket->exists());

        $this->runCommand('buckets', [
            'bucket' => $bucketName,
            '--create' => true,
        ]);

        $bucket->reload();
        $this->assertTrue($bucket->exists());

        $output = $this->runCommand('buckets', [
          'bucket' => $bucketName,
          '--metadata' => true,
        ]);

        $this->assertContains("Bucket Metadata:", $output);

        $output = $this->runCommand('buckets', [
            'bucket' => $bucketName,
            '--delete' => true,
        ]);

        $this->assertFalse($bucket->exists());

        $this->assertContains("Bucket deleted: $bucketName", $output);
    }

    public function testBucketDefaultAcl()
    {
        $output = $this->runCommand('bucket-default-acl', [
            'bucket' => self::$tempBucket->name(),
        ]);

        $this->assertContains(": OWNER", $output);
    }

    public function testManageBucketDefaultAcl()
    {
        $bucketName = self::$tempBucket->name();
        $acl = self::$tempBucket->defaultAcl();

        $output = $this->runCommand('bucket-default-acl', [
            'bucket' => $bucketName,
            '--entity' => 'allAuthenticatedUsers',
            '--create' => true
        ]);

        $aclInfo = $acl->get(['entity' => 'allAuthenticatedUsers']);
        $this->assertArrayHasKey('role', $aclInfo);
        $this->assertEquals('READER', $aclInfo['role']);

        $output .= $this->runCommand('bucket-default-acl', [
            'bucket' => $bucketName,
            '--entity' => 'allAuthenticatedUsers'
        ]);

        $output .= $this->runCommand('bucket-default-acl', [
            'bucket' => $bucketName,
            '--entity' => 'allAuthenticatedUsers',
            '--delete' => true
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

        $output = $this->runCommand('bucket-labels', [
            'bucket' => self::$bucketName,
            'label' => $label1,
            '--value' => $value1
        ]);

        $this->assertEquals(sprintf(
            'Added label %s (%s) to %s' . PHP_EOL,
            $label1,
            $value1,
            self::$bucketName
        ), $output);

        $output = $this->runCommand('bucket-labels', [
            'bucket' => self::$bucketName
        ]);

        $this->assertContains(sprintf('%s: value1', $label1), $output);

        $output = $this->runCommand('bucket-labels', [
            'bucket' => self::$bucketName,
            'label' => $label2,
            '--value' => $value2,
        ]);

        $this->assertEquals(sprintf(
            'Added label %s (%s) to %s' . PHP_EOL,
            $label2,
            $value2,
            self::$bucketName
        ), $output);

        $output = $this->runCommand('bucket-labels', [
            'bucket' => self::$bucketName
        ]);

        $this->assertContains(sprintf('%s: %s', $label1, $value1), $output);
        $this->assertContains(sprintf('%s: %s', $label2, $value2), $output);

        $output = $this->runCommand('bucket-labels', [
            'bucket' => self::$bucketName,
            'label' => $label1,
            '--value' => $value3
        ]);

        $this->assertEquals(sprintf(
            'Added label %s (%s) to %s' . PHP_EOL,
            $label1,
            $value3,
            self::$bucketName
        ), $output);

        $output = $this->runCommand('bucket-labels', [
            'bucket' => self::$bucketName
        ]);

        $this->assertContains(sprintf('%s: %s', $label1, $value3), $output);
        $this->assertNotContains($value1, $output);

        $output = $this->runCommand('bucket-labels', [
            'bucket' => self::$bucketName,
            'label' => $label1,
            '--remove' => true
        ]);

        $this->assertEquals(sprintf(
            'Removed label %s from %s' . PHP_EOL,
            $label1,
            self::$bucketName
        ), $output);

        $output = $this->runCommand('bucket-labels', [
            'bucket' => self::$bucketName,
            'label' => $label2,
            '--remove' => true
        ]);

        $this->assertEquals(sprintf(
            'Removed label %s from %s' . PHP_EOL,
            $label2,
            self::$bucketName
        ), $output);

        $output = $this->runCommand('bucket-labels', [
            'bucket' => self::$bucketName
        ]);

        $this->assertNotContains($label1, $output);
        $this->assertNotContains($label2, $output);
    }

    public function testGenerateEncryptionKey()
    {
        $output = $this->runCommand('encryption', [
            '--generate-key' => true
        ]);

        $this->assertContains("Your encryption key:", $output);
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

        $output = $this->runCommand('encryption', [
            'bucket' => self::$bucketName,
            'object' => $objectName,
            '--key'  => $key,
            '--upload-from' => $uploadFrom,
        ]);

        $output .= $this->runCommand('encryption', [
            'bucket' => self::$bucketName,
            'object' => $objectName,
            '--key'  => $key,
            '--download-to' => $downloadTo,
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

        $output = $this->runCommand('encryption', [
            'bucket' => self::$bucketName,
            'object' => $objectName,
            '--key'  => $key,
            '--upload-from' => $uploadFrom,
        ]);

        $output .= $this->runCommand('encryption', [
            'bucket' => self::$bucketName,
            'object' => $objectName,
            '--key'  => $key,
            '--rotate-key' => $newKey,
        ]);

        $output .= $this->runCommand('encryption', [
            'bucket' => self::$bucketName,
            'object' => $objectName,
            '--key'  => $newKey,
            '--download-to' => $downloadTo,
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
        $objectName = $this->requireEnv('GOOGLE_STORAGE_OBJECT') . '.encrypted';
        $invalidKey = base64_encode(random_bytes(32));
        $downloadTo = tempnam(sys_get_temp_dir(), '/tests');

        try {
            $output = $this->runCommand('encryption', [
                'bucket' => self::$bucketName,
                'object' => $objectName,
                '--key'  => $invalidKey,
                '--download-to' => $downloadTo,
            ]);
            $this->fail('An exception should have been thrown');
        } catch (BadRequestException $e) {
            // Expected exception
        }

        $this->assertContains(
            'The provided encryption key is incorrect',
            $e->getMessage()
        );
    }

    public function testEnableDefaultKmsKey()
    {
        $kmsEncryptedBucketName = self::$bucketName . '-kms-encrypted';

        $output = $this->runCommand('enable-default-kms-key', [
            'project' => self::$projectId,
            'bucket' => $kmsEncryptedBucketName,
            'kms-key-name' => $this->keyName(),
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

        $output = $this->runCommand('upload-with-kms-key', [
            'project' => self::$projectId,
            'bucket' => $kmsEncryptedBucketName,
            'object' => $objectName,
            'upload-from' => $uploadFrom,
            'kms-key-name' => $this->keyName(),
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
