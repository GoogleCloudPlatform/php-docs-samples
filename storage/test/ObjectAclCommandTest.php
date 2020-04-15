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
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for ObjectAclCommand.
 */
class ObjectAclCommandTest extends TestCase
{
    use TestTrait, ExecuteCommandTrait;

    private static $storage;
    private static $bucketName;
    private static $commandFile = __DIR__ . '/../storage.php';

    public static function setUpBeforeClass()
    {
        self::$storage = new StorageClient();
        self::$bucketName = sprintf(
            '%s-legacy',
            self::requireEnv('GOOGLE_STORAGE_BUCKET')
        );
    }

    public function testObjectAcl()
    {
        $objectName = $this->requireEnv('GOOGLE_STORAGE_OBJECT');

        $output = $this->runCommand('object-acl', [
            'bucket' => self::$bucketName,
            'object' => $objectName,
        ]);

        $this->assertContains(': OWNER', $output);
    }

    public function testManageObjectAcl()
    {
        $objectName = $this->requireEnv('GOOGLE_STORAGE_OBJECT');

        $bucket = self::$storage->bucket(self::$bucketName);
        $object = $bucket->object($objectName);
        $acl = $object->acl();

        $output = $this->runCommand('object-acl', [
            'bucket' => self::$bucketName,
            'object' => $objectName,
            '--entity' => 'allAuthenticatedUsers',
            '--create' => true,
        ]);

        $aclInfo = $acl->get(['entity' => 'allAuthenticatedUsers']);
        $this->assertArrayHasKey('role', $aclInfo);
        $this->assertEquals('READER', $aclInfo['role']);

        $output .= $this->runCommand('object-acl', [
            'bucket' => self::$bucketName,
            'object' => $objectName,
            '--entity' => 'allAuthenticatedUsers',
        ]);

        $output .= $this->runCommand('object-acl', [
            'bucket' => self::$bucketName,
            'object' => $objectName,
            '--entity' => 'allAuthenticatedUsers',
            '--delete' => true,
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
}
