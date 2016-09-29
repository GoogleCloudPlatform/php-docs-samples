<?php
/**
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Samples\Storage\Tests;

use Google\Cloud\Samples\Storage;
use Google\Cloud\Samples\Storage\AclCommand;
use Google\Cloud\Samples\Storage\ObjectCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for AclCommand.
 */
class AclCommandTest extends \PHPUnit_Framework_TestCase
{
    protected static $hasCredentials;
    protected $commandTester;

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
    }

    public function setUp()
    {
        $application = new Application();
        $application->add(new AclCommand());
        $this->commandTester = new CommandTester($application->get('acl'));
    }

    public function testBucketAcl()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
        if (!$bucketName = getenv('GOOGLE_STORAGE_BUCKET')) {
            $this->markTestSkipped('No storage bucket name.');
        }

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex("/: OWNER/");
    }

    public function testDefaultBucketAcl()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
        if (!$bucketName = getenv('GOOGLE_STORAGE_BUCKET')) {
            $this->markTestSkipped('No storage bucket name.');
        }

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                '--default' => true,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex("/: OWNER/");
    }

    public function testObjectAcl()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
        if (!$bucketName = getenv('GOOGLE_STORAGE_BUCKET')) {
            $this->markTestSkipped('No storage bucket name.');
        }
        if (!$objectName = getenv('GOOGLE_STORAGE_OBJECT')) {
            $this->markTestSkipped('No storage object name.');
        }

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                'object' => $objectName,
                '--default' => true,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex("/: OWNER/");
    }

    public function testManageObjectAcl()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
        if (!$bucketName = getenv('GOOGLE_STORAGE_BUCKET')) {
            $this->markTestSkipped('No storage bucket name.');
        }
        if (!$objectName = getenv('GOOGLE_STORAGE_OBJECT')) {
            $this->markTestSkipped('No storage object name.');
        }

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                'object' => $objectName,
                '--entity' => 'allAuthenticatedUsers',
                '--create' => true,
            ],
            ['interactive' => false]
        );
        $this->expectOutputRegex("/Added allAuthenticatedUsers (READER) to \S+ ACL/");

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                'object' => $objectName,
                '--entity' => 'allAuthenticatedUsers',
            ],
            ['interactive' => false]
        );
        $this->expectOutputRegex("/allAuthenticatedUsers: READER/");

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                'object' => $objectName,
                '--entity' => 'allAuthenticatedUsers',
                '--delete' => true,
            ],
            ['interactive' => false]
        );
        $this->expectOutputRegex("/Deleted allAuthenticatedUsers from \S+ ACL/");
    }

    public function testManageBucketAcl()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
        if (!$bucketName = getenv('GOOGLE_STORAGE_BUCKET')) {
            $this->markTestSkipped('No storage bucket name.');
        }

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                '--entity' => 'allAuthenticatedUsers',
                '--create' => true,
            ],
            ['interactive' => false]
        );
        $this->expectOutputRegex("/Added allAuthenticatedUsers (READER) to \S+ ACL/");

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                '--entity' => 'allAuthenticatedUsers',
            ],
            ['interactive' => false]
        );
        $this->expectOutputRegex("/allAuthenticatedUsers: READER/");

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                '--entity' => 'allAuthenticatedUsers',
                '--delete' => true,
            ],
            ['interactive' => false]
        );
        $this->expectOutputRegex("/Deleted allAuthenticatedUsers from \S+ ACL/");
    }

    public function testManageBucketDefaultAcl()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
        if (!$bucketName = getenv('GOOGLE_STORAGE_BUCKET')) {
            $this->markTestSkipped('No storage bucket name.');
        }

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                '--entity' => 'allAuthenticatedUsers',
                '--create' => true,
                '--default' => true,
            ],
            ['interactive' => false]
        );
        $this->expectOutputRegex("/Added allAuthenticatedUsers (READER) to \S+ default ACL/");

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                '--entity' => 'allAuthenticatedUsers',
                '--default' => true,
            ],
            ['interactive' => false]
        );
        $this->expectOutputRegex("/allAuthenticatedUsers: READER/");

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                '--entity' => 'allAuthenticatedUsers',
                '--delete' => true,
                '--default' => true,
            ],
            ['interactive' => false]
        );
        $this->expectOutputRegex("/Deleted allAuthenticatedUsers from \S+ default ACL/");
    }
}
