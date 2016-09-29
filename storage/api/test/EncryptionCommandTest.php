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
use Google\Cloud\Samples\Storage\EncryptionCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for EncryptionCommand.
 */
class EncryptionCommandTest extends \PHPUnit_Framework_TestCase
{
    protected static $hasCredentials;

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
    }

    public function setUp()
    {
        $application = new Application();
        $application->add(new EncryptionCommand());
        $this->commandTester = new CommandTester($application->get('encryption'));
    }

    public function testGenerateEncryptionKey()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }

        $this->commandTester->execute(
            [
                '--generate' => true
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex("/Your encryption key:/");
    }

    public function testEncryptedFile()
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
        if (!$bucketName = getenv('GOOGLE_STORAGE_BUCKET')) {
            $this->markTestSkipped('No storage bucket name.');
        }
        if (!$objectName = getenv('GOOGLE_STORAGE_BUCKET')) {
            $this->markTestSkipped('No storage object name.');
        }
        $key = base64_encode(random_bytes(32));
        $uploadFrom = tempnam(sys_get_temp_dir(), '/tests');
        $uploadFromBasename = basename($uploadFrom);
        file_put_contents($uploadFrom, 'foo'.rand());
        $downloadTo = tempnam(sys_get_temp_dir(), '/tests');
        $downloadToBasename = basename($downloadTo);

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                'object' => $objectName,
                '--key'  => $key,
                '--upload-from' => $uploadFrom,
            ],
            ['interactive' => false]
        );
        $this->expectOutputRegex("/Encrypted Object $uploadFromBasename uploaded to \S+/");

        $this->commandTester->execute(
            [
                'bucket' => $bucketName,
                'object' => $objectName,
                '--key'  => $key,
                '--download-to' => $downloadTo,
            ],
            ['interactive' => false]
        );
        $this->expectOutputRegex("/Encrypted object \S+ downloaded to $downloadToBasename/");
    }
}
