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
use Google\Cloud\Storage\StorageClient;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for ObjectSignedUrl.
 *
 * @group storage-signedurl
 */
class ObjectSignedUrlTest extends TestCase
{
    use TestTrait;

    private static $storage;
    private static $bucketName;
    private static $objectName;

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
        $output = self::runFunctionSnippet('get_object_v2_signed_url', [
            self::$bucketName,
            self::$objectName,
        ]);

        $this->assertStringContainsString("The signed url for " . self::$objectName . " is", $output);
    }

    public function testGetV4SignedUrl()
    {
        $output = self::runFunctionSnippet('get_object_v4_signed_url', [
            self::$bucketName,
            self::$objectName,
        ]);

        $this->assertStringContainsString('Generated GET signed URL:', $output);
    }

    public function testGetV4UploadSignedUrl()
    {
        $uploadObjectName = sprintf('test-upload-object-%s', time());

        $output = self::runFunctionSnippet('upload_object_v4_signed_url', [
            self::$bucketName,
            self::$objectName,
        ]);

        $this->assertStringContainsString('Generated PUT signed URL:', $output);

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
