<?php
/**
 * Copyright 2017 Google Inc.
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

use Google\Cloud\Samples\Storage\RequesterPaysCommand;
use Google\Cloud\Storage\StorageClient;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for RequesterPaysCommand.
 */
class RequesterPaysCommandTest extends \PHPUnit_Framework_TestCase
{
    protected static $hasCredentials;
    protected $commandTester;
    protected $storage;

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
    }

    public function setUp()
    {
        $application = require __DIR__ . '/../storage.php';
        $this->commandTester = new CommandTester($application->get('requester-pays'));
        $this->storage = new StorageClient();
    }

    public function testEnableRequesterPays()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
        if (!$bucketName = getenv('GOOGLE_STORAGE_BUCKET')) {
            $this->markTestSkipped('Please set GOOGLE_STORAGE_BUCKET.');
        }
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('Please set GOOGLE_PROJECT_ID.');
        }

        $this->commandTester->execute(
            [
                'project' => $projectId,
                'bucket' => $bucketName,
                '--enable' => true,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex("/Requester pays has been enabled/");
    }

    /** @depends testEnableRequesterPays */
    public function testDisableRequesterPays()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
        if (!$bucketName = getenv('GOOGLE_STORAGE_BUCKET')) {
            $this->markTestSkipped('Please set GOOGLE_STORAGE_BUCKET.');
        }
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('Please set GOOGLE_PROJECT_ID.');
        }

        $this->commandTester->execute(
            [
                'project' => $projectId,
                'bucket' => $bucketName,
                '--disable' => true,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex("/Requester pays has been disabled/");
    }

    /** depends testDisableRequesterPays */
    public function testGetRequesterPaysStatus()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
        if (!$bucketName = getenv('GOOGLE_STORAGE_BUCKET')) {
            $this->markTestSkipped('Please set GOOGLE_STORAGE_BUCKET.');
        }
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('Please set GOOGLE_PROJECT_ID.');
        }

        $this->commandTester->execute(
            [
                'project' => $projectId,
                'bucket' => $bucketName,
                '--check-status' => true,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex("/Requester Pays is disabled/");
    }

    public function testDownloadFileRequesterPays()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
        if (!$bucketName = getenv('GOOGLE_STORAGE_BUCKET')) {
            $this->markTestSkipped('Please set GOOGLE_STORAGE_BUCKET.');
        }
        if (!$objectName = getenv('GOOGLE_STORAGE_OBJECT')) {
            $this->markTestSkipped('Please set GOOGLE_STORAGE_OBJECT.');
        }
        if (!$projectId = getenv('GOOGLE_PROJECT_ID')) {
            $this->markTestSkipped('Please set GOOGLE_PROJECT_ID.');
        }

        // Download to a temp file
        $destination = implode(DIRECTORY_SEPARATOR, [
            sys_get_temp_dir(),
            basename($objectName)
        ]);

        $this->commandTester->execute(
            [
                'project' => $projectId,
                'bucket' => $bucketName,
                'object' => $objectName,
                'download-to' => $destination
            ],
            ['interactive' => false]
        );
        $this->expectOutputRegex("/using requester-pays requests/");
    }
}
