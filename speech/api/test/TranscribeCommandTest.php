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

namespace Google\Cloud\Samples\Speech\Tests;

use Google\Cloud\Samples\Speech\TranscribeCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for TablesCommand.
 */
class TranscribeCommandTest extends \PHPUnit_Framework_TestCase
{
    protected static $hasCredentials;

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
    }

    /** @dataProvider provideTranscribe */
    public function testTranscribe($audioFile, $encoding, $sampleRate)
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }

        $application = new Application();
        $application->add(new TranscribeCommand());
        $commandTester = new CommandTester($application->get('transcribe'));
        $commandTester->execute(
            [
                'audio-file' => $audioFile,
                '--encoding' => $encoding,
                '--sample-rate' => $sampleRate,
            ],
            ['interactive' => false]
        );

        $this->expectOutputRegex("/how old is the Brooklyn Bridge/");
    }

    public function provideTranscribe()
    {
        return [
            [__DIR__ . '/data/audio32KHz.raw', 'LINEAR16', '32000'],
            [__DIR__ . '/data/audio32KHz.flac', 'FLAC', '32000'],
        ];
    }
}
