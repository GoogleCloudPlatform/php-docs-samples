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
use Google\Cloud\Samples\Storage\ObjectAclCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for ObjectAclCommand.
 */
class ObjectAclCommandTest extends \PHPUnit_Framework_TestCase
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
        $application->add(new ObjectAclCommand());
        $this->commandTester = new CommandTester($application->get('object-acl'));
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

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                'object' => $objectName,
                '--entity' => 'allAuthenticatedUsers',
            ],
            ['interactive' => false]
        );

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                'object' => $objectName,
                '--entity' => 'allAuthenticatedUsers',
                '--delete' => true,
            ],
            ['interactive' => false]
        );

        $objectUrl = sprintf('gs://%s/%s', $bucketName, $objectName);
        $outputString = <<<EOF
Added allAuthenticatedUsers (READER) to $objectUrl ACL
allAuthenticatedUsers: READER
Deleted allAuthenticatedUsers from $objectUrl ACL

EOF;
        $this->expectOutputString($outputString);
    }
}
