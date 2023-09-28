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
 */
class ObjectSignedUrlTest extends TestCase
{
    use TestTrait;

    private static $storage;
    private static $bucketName;

    public static function setUpBeforeClass(): void
    {
        self::$storage = new StorageClient();
        self::$bucketName = self::requireEnv('GOOGLE_STORAGE_BUCKET');
    }

    public function testGetV2SignedUrl()
    {
        $object = self::$storage->bucket(self::$bucketName)->upload('test', [
            'name' => uniqid('samples-v2-signed-url-'),
        ]);

        $output = self::runFunctionSnippet('get_object_v2_signed_url', [
            self::$bucketName,
            $object->name(),
        ]);

        $object->delete();

        $this->assertStringContainsString('The signed url for ' . $object->name() . ' is', $output);
    }

    public function testGetV4SignedUrl()
    {
        $object = self::$storage->bucket(self::$bucketName)->upload('test', [
            'name' => uniqid('samples-v4-signed-url-'),
        ]);

        $output = self::runFunctionSnippet('get_object_v4_signed_url', [
            self::$bucketName,
            $object->name(),
        ]);

        $object->delete();

        $this->assertStringContainsString('Generated GET signed URL:', $output);
    }

    public function testGetV4UploadSignedUrl()
    {
        $object = self::$storage->bucket(self::$bucketName)->object(
            uniqid('samples-v4-upload-url-')
        );

        $output = self::runFunctionSnippet('upload_object_v4_signed_url', [
            self::$bucketName,
            $object->name(),
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

        $content = '';
        try {
            // Assert file is correctly uploaded to the bucket.
            $content = $object->downloadAsString();
            $object->delete();
        } catch (\Exception $e) {
        }

        $this->assertEquals(200, $res->getStatusCode());
        $this->assertEquals('upload content', $content);
    }

    public function testGenerateSignedPostPolicy()
    {
        $object = self::$storage->bucket(self::$bucketName)->object(
            uniqid('samples-v4-post-policy-')
        );

        $bucketName = self::$bucketName;
        $output = self::runFunctionSnippet('generate_signed_post_policy_v4', [
            $bucketName,
            $object->name(),
        ]);

        $this->assertStringContainsString("<form action='https://storage.googleapis.com/$bucketName/", $output);
        $this->assertStringContainsString("<input name='key' value='{$object->name()}'", $output);
        $this->assertStringContainsString("<input name='x-goog-signature'", $output);
        $this->assertStringContainsString("<input name='x-goog-date'", $output);
        $this->assertStringContainsString("<input name='x-goog-credential'", $output);
        $this->assertStringContainsString("<input name='x-goog-algorithm' value='GOOG4-RSA-SHA256'", $output);
        $this->assertStringContainsString("<input name='policy'", $output);
        $this->assertStringContainsString("<input name='x-goog-meta-test' value='data'", $output);
        $this->assertStringContainsString("<input type='file' name='file'/>", $output);
    }
}
