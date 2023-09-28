<?php
/**
 * Copyright 2016 Google Inc.
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

use Google\Cloud\Core\Exception\NotFoundException;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for object ACLs.
 */
class ObjectAclTest extends TestCase
{
    use TestTrait;

    private static $storage;
    private static $bucketName;

    public static function setUpBeforeClass(): void
    {
        self::$storage = new StorageClient();
        self::$bucketName = getenv('GOOGLE_STORAGE_BUCKET_LEGACY') ?: sprintf(
            '%s-legacy',
            self::requireEnv('GOOGLE_STORAGE_BUCKET')
        );
    }

    public function testObjectAcl()
    {
        $objectName = $this->requireEnv('GOOGLE_STORAGE_OBJECT');

        $output = self::runFunctionSnippet('get_object_acl', [
            self::$bucketName,
            $objectName,
        ]);

        $this->assertStringContainsString(': OWNER', $output);
    }

    public function testManageObjectAcl()
    {
        $objectName = $this->requireEnv('GOOGLE_STORAGE_OBJECT');

        $bucket = self::$storage->bucket(self::$bucketName);
        $object = $bucket->object($objectName);
        $acl = $object->acl();

        $output = self::runFunctionSnippet('add_object_acl', [
            self::$bucketName,
            $objectName,
            'allAuthenticatedUsers',
            'READER',
        ]);

        $aclInfo = $acl->get(['entity' => 'allAuthenticatedUsers']);
        $this->assertArrayHasKey('role', $aclInfo);
        $this->assertEquals('READER', $aclInfo['role']);

        $output .= self::runFunctionSnippet('get_object_acl_for_entity', [
            self::$bucketName,
            $objectName,
            'allAuthenticatedUsers',
        ]);

        $output .= self::runFunctionSnippet('delete_object_acl', [
            self::$bucketName,
            $objectName,
            'allAuthenticatedUsers',
        ]);

        try {
            $acl->get(['entity' => 'allAuthenticatedUsers']);
            $this->fail();
        } catch (NotFoundException $e) {
            $this->assertTrue(true);
        }

        $objectUrl = sprintf('gs://%s/%s', self::$bucketName, $objectName);
        $outputString = <<<EOF
Added allAuthenticatedUsers (READER) to $objectUrl ACL
allAuthenticatedUsers: READER
Deleted allAuthenticatedUsers from $objectUrl ACL

EOF;
        $this->assertEquals($output, $outputString);
    }

    public function testPrintFileAclForUser()
    {
        $objectName = $this->requireEnv('GOOGLE_STORAGE_OBJECT');

        $bucket = self::$storage->bucket(self::$bucketName);
        $object = $bucket->object($objectName);
        $acl = $object->acl();

        $output = self::runFunctionSnippet('add_object_acl', [
            self::$bucketName,
            $objectName,
            'allAuthenticatedUsers',
            'READER',
        ]);

        $aclInfo = $acl->get(['entity' => 'allAuthenticatedUsers']);
        $this->assertArrayHasKey('role', $aclInfo);
        $this->assertEquals('READER', $aclInfo['role']);

        $output .= self::runFunctionSnippet('print_file_acl_for_user', [
            self::$bucketName,
            $objectName,
            'allAuthenticatedUsers',
        ]);

        $objectUrl = sprintf('gs://%s/%s', self::$bucketName, $objectName);
        $outputString = <<<EOF
Added allAuthenticatedUsers (READER) to $objectUrl ACL
allAuthenticatedUsers: READER

EOF;
        $this->assertEquals($output, $outputString);
    }

    public function testPrintBucketAclForUser()
    {
        $objectName = $this->requireEnv('GOOGLE_STORAGE_OBJECT');

        $bucket = self::$storage->bucket(self::$bucketName);
        $object = $bucket->object($objectName);
        $acl = $object->acl();

        $output = self::runFunctionSnippet('add_bucket_acl', [
            self::$bucketName,
            'allAuthenticatedUsers',
            'READER',
        ]);

        $aclInfo = $acl->get(['entity' => 'allAuthenticatedUsers']);
        $this->assertArrayHasKey('role', $aclInfo);
        $this->assertEquals('READER', $aclInfo['role']);

        $output .= self::runFunctionSnippet('print_bucket_acl_for_user', [
            self::$bucketName,
            'allAuthenticatedUsers',
        ]);

        $bucketUrl = sprintf('gs://%s', self::$bucketName);
        $outputString = <<<EOF
Added allAuthenticatedUsers (READER) to $bucketUrl ACL
allAuthenticatedUsers: READER

EOF;
        $this->assertEquals($output, $outputString);
    }
}
