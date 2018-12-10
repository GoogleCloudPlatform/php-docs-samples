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
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for RequesterPaysCommand.
 */
class RequesterPaysCommandTest extends TestCase
{
    use TestTrait;
    use ExecuteCommandTrait;

    private static $bucketName;
    private static $commandFile = __DIR__ . '/../storage.php';

    /** @beforeClass */
    public static function getBucketName()
    {
        self::$bucketName = self::requireEnv('GOOGLE_REQUESTER_PAYS_STORAGE_BUCKET');
    }

    public function testEnableRequesterPays()
    {
        $output = $this->runCommand('requester-pays', [
            'project' => self::$projectId,
            'bucket' => self::$bucketName,
            '--enable' => true,
        ]);

        $this->assertContains("Requester pays has been enabled", $output);
    }

    /** @depends testEnableRequesterPays */
    public function testDisableRequesterPays()
    {
        $output = $this->runCommand('requester-pays', [
            'project' => self::$projectId,
            'bucket' => self::$bucketName,
            '--disable' => true,
        ]);

        $this->assertContains("Requester pays has been disabled", $output);
    }

    /** depends testDisableRequesterPays */
    public function testGetRequesterPaysStatus()
    {
        $output = $this->runCommand('requester-pays', [
            'project' => self::$projectId,
            'bucket' => self::$bucketName,
            '--check-status' => true,
        ]);

        $this->assertContains("Requester Pays is disabled", $output);
    }

    public function testDownloadFileRequesterPays()
    {
        $objectName = $this->requireEnv('GOOGLE_STORAGE_OBJECT');

        // Download to a temp file
        $destination = implode(DIRECTORY_SEPARATOR, [
            sys_get_temp_dir(),
            basename($objectName)
        ]);

        $output = $this->runCommand('requester-pays', [
            'project' => self::$projectId,
            'bucket' => self::$bucketName,
            'object' => $objectName,
            'download-to' => $destination,
        ]);
        $this->assertContains("using requester-pays requests", $output);
    }
}
