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
use Google\Cloud\Samples\Storage\ObjectsCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for ObjectsCommand.
 */
class ObjectsCommandTest extends \PHPUnit_Framework_TestCase
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
        $application->add(new ObjectsCommand());
        $this->commandTester = new CommandTester($application->get('objects'));
    }

    public function testListObjects()
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

        $this->expectOutputRegex("/Object:/");
    }

    public function testManageObject()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
        if (!$bucketName = getenv('GOOGLE_STORAGE_BUCKET')) {
            $this->markTestSkipped('No storage bucket name.');
        }
        $objectName = 'test-object-' . time();
        $uploadFrom = tempnam(sys_get_temp_dir(), '/tests');
        $basename = basename($uploadFrom);
        file_put_contents($uploadFrom, 'foo' . rand());
        $downloadTo = tempnam(sys_get_temp_dir(), '/tests');
        $downloadToBasename = basename($downloadTo);

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                'object' => $objectName,
                '--upload-from' => $uploadFrom,
            ],
            ['interactive' => false]
        );
        $this->expectOutputRegex("/Uploaded $basename to \S+/");

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                'object' => $objectName,
                '--copy-to' => $objectName . '-copy',
            ],
            ['interactive' => false]
        );
        $this->expectOutputRegex("/Copied \S+$basename to \S+$basename-copy/");

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                'object' => $objectName . '-copy',
                '--delete' => true,
            ],
            ['interactive' => false]
        );
        $this->expectOutputRegex("/Deleted \S+$basename-copy/");

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                'object' => $objectName,
                '--make-public' => true,
            ],
            ['interactive' => false]
        );
        $this->expectOutputRegex("/\S+$basename-moved is now public/");

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                'object' => $objectName,
                '--make-public' => true,
            ],
            ['interactive' => false]
        );
        $this->expectOutputRegex("/\S+$basename-moved is now public/");

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                'object' => $objectName,
                '--download-to' => $downloadTo,
            ],
            ['interactive' => false]
        );
        $this->expectOutputRegex("/Downloaded \S+$basename to $downloadToBasename/");

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                'object' => $objectName,
                '--move-to' => $objectName . '-moved',
            ],
            ['interactive' => false]
        );
        $this->expectOutputRegex("/Copied \S+$basename to \S+$objectName-moved/");

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                'object' => $objectName . '-moved',
                '--delete' => true,
            ],
            ['interactive' => false]
        );
        $this->expectOutputRegex("/Deleted \S+$objectName-moved/");
    }
}
