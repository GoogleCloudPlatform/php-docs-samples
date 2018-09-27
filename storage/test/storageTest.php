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

use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\ExecuteCommandTrait;

/**
 * Unit Tests for storage commands.
 */
class storageTest extends \PHPUnit_Framework_TestCase
{
    use TestTrait;
    use ExecuteCommandTrait;

    private static $commandFile = __DIR__ . '/../storage.php';
    private $bucketName;
    private $keyName;

    public function setUp()
    {
        $this->keyName = sprintf(
            'projects/%s/locations/us/keyRings/%s/cryptoKeys/%s',
            self::$projectId,
            $this->requireEnv('GOOGLE_STORAGE_KMS_KEYRING'),
            $this->requireEnv('GOOGLE_STORAGE_KMS_CRYPTOKEY')
        );
        $this->bucketName = $this->requireEnv('GOOGLE_STORAGE_BUCKET');
    }

    public function testEnableDefaultKmsKey()
    {
        $kmsEncryptedBucketName = $this->bucketName . '-kms-encrypted';

        $output = $this->runCommand('enable-default-kms-key', [
            'project' => self::$projectId,
            'bucket' => $kmsEncryptedBucketName,
            'kms-key-name' => $this->keyName,
        ]);

        $this->assertEquals($output, sprintf(
            'Default KMS key for %s was set to %s' . PHP_EOL,
            $kmsEncryptedBucketName,
            $this->keyName
        ));
    }

    /** @depends testEnableDefaultKmsKey */
    public function testUploadWithKmsKey()
    {
        $kmsEncryptedBucketName = $this->bucketName . '-kms-encrypted';

        $objectName = 'test-object-' . time();
        $uploadFrom = tempnam(sys_get_temp_dir(), '/tests');
        file_put_contents($uploadFrom, 'foo' . rand());

        $output = $this->runCommand('upload-with-kms-key', [
            'project' => self::$projectId,
            'bucket' => $kmsEncryptedBucketName,
            'object' => $objectName,
            'upload-from' => $uploadFrom,
            'kms-key-name' => $this->keyName,
        ]);

        $this->assertEquals($output, sprintf(
            'Uploaded %s to gs://%s/%s using encryption key %s' . PHP_EOL,
            basename($uploadFrom),
            $kmsEncryptedBucketName,
            $objectName,
            $this->keyName
        ));
    }
}
