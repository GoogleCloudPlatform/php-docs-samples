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

use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for storage commands.
 */
class storageTest extends \PHPUnit_Framework_TestCase
{
    private $serviceAccountPath;
    private $bucketName;
    private $projectId;

    public function setUp()
    {
        if (!$serviceAccountPath = getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            $this->markTestSkipped('Set the GOOGLE_APPLICATION_CREDENTIALS ' .
                'environment variable');
        }
        if (!$bucketName = getenv('GOOGLE_STORAGE_BUCKET')) {
            $this->markTestSkipped('Set the GOOGLE_STORAGE_BUCKET ' .
                'environment variable');
        }
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('Set the GOOGLE_PROJECT_ID ' .
                'environment variable');
        }
        $this->serviceAccountPath = $serviceAccountPath;
        $this->bucketName = $bucketName;
        $this->projectId = $projectId;
    }

    public function testEnableDefaultKmsKey()
    {
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('Please set GOOGLE_PROJECT_ID.');
        }
        if (!$bucketName = getenv('GOOGLE_STORAGE_BUCKET')) {
            $this->markTestSkipped('Please set GOOGLE_STORAGE_BUCKET.');
        }
        if (!$kmsKeyName = getenv('GOOGLE_STORAGE_KMS_KEYNAME')) {
            return $this->markTestSkipped('Set the GOOGLE_STORAGE_KMS_KEYNAME environment variable');
        }

        $kmsEncryptedBucketName = $bucketName . '-kms-encrypted';

        $output = $this->runCommand('enable-default-kms-key', [
            'project' => $projectId,
            'bucket' => $kmsEncryptedBucketName,
            'kms-key-name' => $kmsKeyName,
        ]);

        $this->assertEquals($output, sprintf(
            'Default KMS key for %s was set to %s' . PHP_EOL,
            $kmsEncryptedBucketName,
            $kmsKeyName
        ));
    }

    /** @depends testEnableDefaultKmsKey */
    public function testUploadWithKmsKey()
    {
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('Please set GOOGLE_PROJECT_ID.');
        }
        if (!$bucketName = getenv('GOOGLE_STORAGE_BUCKET')) {
            $this->markTestSkipped('Please set GOOGLE_STORAGE_BUCKET.');
        }
        if (!$kmsKeyName = getenv('GOOGLE_STORAGE_KMS_KEYNAME')) {
            return $this->markTestSkipped('Set the GOOGLE_STORAGE_KMS_KEYNAME environment variable');
        }

        $kmsEncryptedBucketName = $bucketName . '-kms-encrypted';

        $objectName = 'test-object-' . time();
        $uploadFrom = tempnam(sys_get_temp_dir(), '/tests');
        file_put_contents($uploadFrom, 'foo' . rand());

        $output = $this->runCommand('upload-with-kms-key', [
            'project' => $projectId,
            'bucket' => $kmsEncryptedBucketName,
            'object' => $objectName,
            'upload-from' => $uploadFrom,
            'kms-key-name' => $kmsKeyName,
        ]);

        $this->assertEquals($output, sprintf(
            'Uploaded %s to gs://%s/%s using encryption key %s' . PHP_EOL,
            basename($uploadFrom),
            $kmsEncryptedBucketName,
            $objectName,
            $kmsKeyName
        ));
    }

    private function runCommand($commandName, $args = [])
    {
        $application = require __DIR__ . '/../storage.php';
        $command = $application->get($commandName);
        $commandTester = new CommandTester($command);

        ob_start();
        $commandTester->execute(
            $args,
            ['interactive' => false]
        );

        return ob_get_clean();
    }
}
