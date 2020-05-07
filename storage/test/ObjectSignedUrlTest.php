<?php
/**
 * Copyright 2019 Google LLC.
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

namespace Google\Cloud\Samples\Storage\Tests;

use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use Google\Cloud\Storage\StorageClient;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for ObjectSignedUrl.
 */
class ObjectSignedUrlTest extends TestCase
{
    use TestTrait;
    use ExecuteCommandTrait;

    private static $storage;
    private static $bucketName;
    private static $objectName;
    private static $commandFile = __DIR__ . '/../storage.php';

    /** @beforeClass */
    public static function setUpObject()
    {
        self::$storage = new StorageClient();
        self::$bucketName = self::requireEnv('GOOGLE_STORAGE_BUCKET');
        self::$objectName = sprintf('test-object-%s', time());
        // Pre-upload an object for testing GET signed urls
        self::$storage
            ->bucket(self::$bucketName)
            ->upload("test file content", [
                'name' => self::$objectName
            ]);
    }

    public function testGetV2SignedUrl()
    {
        $output = $this->runCommand('get-object-v2-signed-url', [
            'bucket' => self::$bucketName,
            'object' => self::$objectName,
        ]);

        $this->assertContains("The signed url for " . self::$objectName . " is", $output);
    }

    public function testGetV4SignedUrl()
    {
        $output = $this->runCommand('get-object-v4-signed-url', [
            'bucket' => self::$bucketName,
            'object' => self::$objectName,
        ]);

        $this->assertContains('Generated GET signed URL:', $output);
    }

    public function testGetV4UploadSignedUrl()
    {
        $uploadObjectName = sprintf('test-upload-object-%s', time());

        $output = $this->runCommand('get-object-v4-upload-signed-url', [
            'bucket' => self::$bucketName,
            'object' => $uploadObjectName,
        ]);

        $this->assertContains('Generated PUT signed URL:', $output);

        // Extract the signed URL from command output.
        preg_match_all('/URL:\n([^\s]+)/', $output, $matches);
        $url = $matches[1][0];

        // Make a PUT request using the signed URL.
        $client = new Client();
        $res = $client->request('PUT', $url, [
            'headers' => [
                'Content-Type' => 'application/octet-stream',
            ],
            'body' => 'upload content'
        ]);

        $this->assertEquals(200, $res->getStatusCode());

        // Assert file is correctly uploaded to the bucket.
        $content = self::$storage
            ->bucket(self::$bucketName)
            ->object($uploadObjectName)
            ->downloadAsString();
        $this->assertEquals('upload content', $content);
    }
}
