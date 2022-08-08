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
use Google\Cloud\Core\Exception\BadRequestException;
use Google\Cloud\Core\Exception\NotFoundException;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
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
        foreach (self::$tempBucket->objects(['versions' => true]) as $object) {
            $object->delete();
        }
        self::$tempBucket->delete();
    }

    public function testBucketAcl()
    {
        $output = $this->runFunctionSnippet('get_bucket_acl', [
            self::$tempBucket->name(),
        ]);

        $this->assertRegExp('/: OWNER/', $output);
    }

    /**
     * @return void
     */
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

        $this->assertStringContainsString('Bucket:', $output);
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

        $this->assertStringContainsString('Bucket Metadata:', $output);

        $output = $this->runFunctionSnippet('delete_bucket', [$bucketName]);

        $this->assertFalse($bucket->exists());

        $this->assertStringContainsString("Bucket deleted: $bucketName", $output);
    }

    public function testBucketDefaultAcl()
    {
        $output = $this->runFunctionSnippet('get_bucket_default_acl', [
            self::$tempBucket->name(),
        ]);

        $this->assertStringContainsString(': OWNER', $output);
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
        $this->assertStringContainsString(
            sprintf('Added allAuthenticatedUsers (READER) to %s default ACL', $bucketUrl),
            $output
        );
        $this->assertStringContainsString(
            'allAuthenticatedUsers: READER',
            $output
        );
        $this->assertStringContainsString(
            sprintf('Deleted allAuthenticatedUsers from %s default ACL', $bucketUrl),
            $output
        );
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

        $this->assertStringContainsString('Your encryption key:', $output);
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
        $this->assertStringContainsString(
            sprintf('Uploaded encrypted %s to %s', $uploadFromBasename, $objectUrl),
            $output
        );
        $this->assertStringContainsString(
            sprintf('Encrypted object %s downloaded to %s', $objectUrl, $downloadToBasename),
            $output
        );
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
        $this->assertStringContainsString(
            sprintf('Uploaded encrypted %s to %s', $uploadFromBasename, $objectUrl),
            $output
        );
        $this->assertStringContainsString(
            sprintf('Rotated encryption key for object %s', $objectUrl),
            $output
        );
        $this->assertStringContainsString(
            sprintf('Encrypted object %s downloaded to %s', $objectUrl, $downloadToBasename),
            $output
        );
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

    public function testBucketVersioning()
    {
        $output = self::runFunctionSnippet('enable_versioning', [
            self::$bucketName,
        ]);

        $this->assertEquals(
            sprintf('Versioning is now enabled for bucket %s', self::$bucketName),
            $output,
        );

        $output = self::runFunctionSnippet('disable_versioning', [
            self::$bucketName,
        ]);

        $this->assertEquals(
            sprintf('Versioning is now disabled for bucket %s', self::$bucketName),
            $output,
        );
    }

    public function testBucketWebsiteConfiguration()
    {
        $bucket = self::$storage->createBucket(uniqid('samples-website-configuration-'));
        $obj = $bucket->upload('test', [
            'name' => 'test.html'
        ]);

        $output = self::runFunctionSnippet('define_bucket_website_configuration', [
            $bucket->name(),
            $obj->name(),
            $obj->name(),
        ]);

        $info = $bucket->reload();
        $obj->delete();
        $bucket->delete();

        $this->assertEquals(
            sprintf(
                'Static website bucket %s is set up to use %s as the index page and %s as the 404 page.',
                $bucket->name(),
                $obj->name(),
                $obj->name(),
            ),
            $output
        );

        $this->assertEquals($obj->name(), $info['website']['mainPageSuffix']);
        $this->assertEquals($obj->name(), $info['website']['notFoundPage']);
    }

    public function testGetServiceAccount()
    {
        $output = self::runFunctionSnippet('get_service_account', [
            self::$projectId,
        ]);

        $this->assertStringContainsString(
            sprintf('The GCS service account email for project %s is ', self::$projectId),
            $output
        );
    }

    public function testCorsConfiguration()
    {
        $bucket = self::$storage->createBucket(uniqid('samples-cors-configuration-'));

        $method = 'GET';
        $origin = 'https://google.com';
        $responseHeader = 'Content-Type';
        $maxAgeSeconds = 10;

        $output = self::runFunctionSnippet('cors_configuration', [
            $bucket->name(),
            $method,
            $origin,
            $responseHeader,
            $maxAgeSeconds,
        ]);

        $info = $bucket->reload();

        $removeOutput = self::runFunctionSnippet('remove_cors_configuration', [
            $bucket->name(),
        ]);
        $removeInfo = $bucket->reload();

        $bucket->delete();

        $this->assertEquals([$method], $info['cors'][0]['method']);
        $this->assertEquals($maxAgeSeconds, $info['cors'][0]['maxAgeSeconds']);
        $this->assertEquals([$responseHeader], $info['cors'][0]['responseHeader']);

        $this->assertEquals(
            sprintf(
                'Bucket %s was updated with a CORS config to allow GET requests from ' .
                '%s sharing %s responses across origins.',
                $bucket->name(),
                $origin,
                $responseHeader
            ),
            $output
        );

        $this->assertArrayNotHasKey('cors', $removeInfo);
        $this->assertEquals(
            sprintf('Removed CORS configuration from bucket %s', $bucket->name()),
            $removeOutput
        );
    }

    public function testListFileArchivedGenerations()
    {
        $bucket = self::$storage->createBucket(uniqid('samples-list-file-archived-generations-'), [
            'versioning' => [
                'enabled' => true,
            ]
        ]);

        $objectv1 = $bucket->upload('v1', [
            'name' => 'test.txt',
        ]);

        $objectv2 = $bucket->upload('v2', [
            'name' => 'test.txt',
        ]);

        $output = self::runFunctionSnippet('list_file_archived_generations', [
            $bucket->name(),
        ]);

        foreach ($bucket->objects(['versions' => true]) as $object) {
            $object->delete();
        }

        $bucket->delete();

        $lines = explode(PHP_EOL, trim($output));
        $this->assertCount(2, $lines);
        $this->assertStringMatchesFormat('test.txt,%d', $lines[0]);
        $this->assertStringMatchesFormat('test.txt,%d', $lines[1]);
    }

    public function testCopyFileArchivedGeneration()
    {
        $bucket = self::$storage->createBucket(uniqid('samples-copy-file-archived-generation-'), [
            'versioning' => [
                'enabled' => true,
            ]
        ]);

        $objectv1 = $bucket->upload('v1', [
            'name' => 'test.txt',
        ]);

        $objectv2 = $bucket->upload('v2', [
            'name' => 'test.txt',
        ]);

        $newObjectName = 'v3.txt';

        $output = self::runFunctionSnippet('copy_file_archived_generation', [
            $bucket->name(),
            $objectv1->name(),
            $objectv1->info()['generation'],
            $newObjectName,
        ]);

        $newObjContents = '';
        try {
            $newObj = $bucket->object($newObjectName);
            $newObjContents = $newObj->downloadAsString();
        } catch (\Exception $e) {
        }

        foreach ($bucket->objects(['versions' => true]) as $object) {
            $object->delete();
        }

        $bucket->delete();

        $this->assertEquals('v1', $newObjContents);
        $this->assertEquals(
            sprintf(
                'Generation %s of object %s in bucket %s was copied to %s',
                $objectv1->info()['generation'],
                $objectv1->name(),
                $bucket->name(),
                $newObjectName
            ),
            $output
        );
    }

    public function testBucketDeleteDefaultKmsKey()
    {
        $bucket = self::$storage->createBucket(uniqid('samples-bucket-delete-default-kms-key-'));

        $output = self::runFunctionSnippet('bucket_delete_default_kms_key', [
            $bucket->name(),
        ]);

        $info = $bucket->reload();

        $bucket->delete();

        $this->assertEquals(sprintf('Default KMS key was removed from %s', $bucket->name()), $output);
        $this->assertArrayNotHasKey('encryption', $info);
    }

    public function testCreateBucketClassLocation()
    {
        $bucketName = uniqid('samples-create-bucket-class-location-');
        $output = self::runFunctionSnippet('create_bucket_class_location', [
            $bucketName,
        ]);

        $bucket = self::$storage->bucket($bucketName);
        $exists = $bucket->exists();
        $bucket->delete();

        $this->assertTrue($exists);
        $this->assertStringContainsString('Created bucket', $output);
    }

    public function testCreateBucketDualRegion()
    {
        $location = 'US';
        $region1 = 'US-EAST1';
        $region2 = 'US-WEST1';
        $locationType = 'dual-region';

        $bucketName = uniqid('samples-create-bucket-dual-region-');
        $output = self::runFunctionSnippet('create_bucket_dual_region', [
            $bucketName,
            $location,
            $region1,
            $region2
        ]);

        $bucket = self::$storage->bucket($bucketName);
        $info = $bucket->reload();
        $exists = $bucket->exists();
        $bucket->delete();

        $this->assertTrue($exists);
        $this->assertStringContainsString($bucketName, $output);
        $this->assertStringContainsString($location, $output);
        $this->assertStringContainsString($locationType, $output);
        $this->assertStringContainsString($region1, $output);
        $this->assertStringContainsString($region2, $output);

        $this->assertEquals($location, $info['location']);
        $this->assertEquals($locationType, $info['locationType']);
        $this->assertArrayHasKey('customPlacementConfig', $info);
        $this->assertArrayHasKey('dataLocations', $info['customPlacementConfig']);
        $this->assertContains($region1, $info['customPlacementConfig']['dataLocations']);
        $this->assertContains($region2, $info['customPlacementConfig']['dataLocations']);
    }

    public function testObjectCsekToCmek()
    {
        $objectName = uniqid('samples-object-csek-to-cmek-');
        $key = base64_encode(random_bytes(32));
        self::$storage->bucket(self::$bucketName)->upload('encrypted', [
            'name' => $objectName,
            'encryptionKey' => $key
        ]);

        $output = self::runFunctionSnippet('object_csek_to_cmek', [
            self::$bucketName,
            $objectName,
            $key,
            $this->keyName(),
        ]);

        $obj2 = self::$storage->bucket(self::$bucketName)->object($objectName);
        $info = $obj2->reload();
        $obj2->delete();

        $this->assertStringContainsString($this->keyName(), $info['kmsKeyName']);
        $this->assertEquals(
            sprintf(
                'Object %s in bucket %s is now managed by the KMS key %s instead of a customer-supplied encryption key',
                $objectName,
                self::$bucketName,
                $this->keyName()
            ),
            $output
        );
    }

    public function testChangeDefaultStorageClass()
    {
        $bucket = self::$storage->createBucket(uniqid('samples-change-default-storage-class-'));

        $output = self::runFunctionSnippet('change_default_storage_class', [
            $bucket->name(),
        ]);

        $info = $bucket->reload();
        $bucket->delete();

        $this->assertEquals('COLDLINE', $info['storageClass']);
        $this->assertEquals(
            sprintf('Default storage class for bucket %s has been set to %s', $bucket->name(), 'COLDLINE'),
            $output
        );
    }

    public function testDeleteFileArchivedGeneration()
    {
        $bucket = self::$storage->createBucket(uniqid('samples-delete-file-archived-generation-'), [
            'versioning' => [
                'enabled' => true,
            ],
        ]);

        $objectName = 'test.txt';

        $obj1 = $bucket->upload('v1', [
            'name' => $objectName,
        ]);

        $generationToDelete = $obj1->info()['generation'];

        $bucket->upload('v2', [
            'name' => $objectName,
        ]);

        $output = self::runFunctionSnippet('delete_file_archived_generation', [
            $bucket->name(),
            $objectName,
            $generationToDelete,
        ]);

        $exists = $obj1->exists();

        foreach ($bucket->objects(['versions' => true]) as $object) {
            $object->delete();
        }

        $bucket->delete();

        $this->assertFalse($exists);
        $this->assertEquals(
            sprintf(
                'Generation %s of object %s was deleted from %s',
                $generationToDelete,
                $objectName,
                $bucket->name()
            ),
            $output
        );
    }

    public function testDownloadPublicObject()
    {
        $bucket = self::$storage->createBucket(uniqid('samples-download-public-object-'));

        self::runFunctionSnippet('set_bucket_public_iam', [
            $bucket->name(),
        ]);

        $object = self::$storage->bucket(self::$bucketName)->upload('test content', [
            'name' => uniqid('samples-download-public-object-'),
        ]);

        $downloadTo = tempnam(sys_get_temp_dir(), '/tests/' . $object->name());

        $output = self::runFunctionSnippet('download_public_file', [
            self::$bucketName,
            $object->name(),
            $downloadTo,
        ]);

        $object->delete();
        $bucket->delete();

        $this->assertEquals(
            sprintf(
                'Downloaded public object %s from bucket %s to %s',
                $object->name(),
                self::$bucketName,
                $downloadTo,
            ),
            $output
        );

        $this->assertFileExists($downloadTo);
    }

    public function testSetClientEndpoint()
    {
        $testEndpoint = 'https://test-endpoint.com';

        $output = self::runFunctionSnippet('set_client_endpoint', [
            self::$projectId,
            $testEndpoint,
        ]);

        $this->assertStringContainsString(sprintf('API endpoint: %s', $testEndpoint), $output);
        $this->assertStringContainsString(sprintf('Base URI: %s/storage/v1/', $testEndpoint), $output);
        $this->assertStringContainsString('Storage Client initialized.', $output);
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
