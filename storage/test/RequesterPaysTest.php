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

use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for requester pays samples.
 */
class RequesterPaysTest extends TestCase
{
    use TestTrait;

    private static $bucketName;

    /** @beforeClass */
    public static function getBucketName()
    {
        self::$bucketName = self::requireEnv('GOOGLE_REQUESTER_PAYS_STORAGE_BUCKET');
    }

    public function testEnableRequesterPays()
    {
        $output = self::runFunctionSnippet('enable_requester_pays', [
            self::$bucketName,
        ]);

        $this->assertStringContainsString('Requester pays has been enabled', $output);
    }

    /** @depends testEnableRequesterPays */
    public function testDisableRequesterPays()
    {
        $output = self::runFunctionSnippet('disable_requester_pays', [
            self::$bucketName,
        ]);

        $this->assertStringContainsString('Requester pays has been disabled', $output);
    }

    /** depends testDisableRequesterPays */
    public function testGetRequesterPaysStatus()
    {
        $output = self::runFunctionSnippet('get_requester_pays_status', [
            self::$bucketName,
        ]);

        $this->assertStringContainsString('Requester Pays is disabled', $output);
    }

    public function testDownloadFileRequesterPays()
    {
        $objectName = $this->requireEnv('GOOGLE_STORAGE_OBJECT');

        // Download to a temp file
        $destination = implode(DIRECTORY_SEPARATOR, [
            sys_get_temp_dir(),
            basename($objectName)
        ]);

        $output = self::runFunctionSnippet('download_file_requester_pays', [
            self::$projectId,
            self::$bucketName,
            $objectName,
            $destination,
        ]);

        $this->assertStringContainsString('using requester-pays requests', $output);
    }
}
