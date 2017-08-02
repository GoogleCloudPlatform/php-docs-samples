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

namespace Google\Cloud\Samples\Speech\Tests;

use Symfony\Component\Console\Tester\CommandTester;

class speechTest extends \PHPUnit_Framework_TestCase
{
    protected static $hasCredentials;
    protected static $bucketName;

    public static function setUpBeforeClass()
    {
        $path = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        self::$hasCredentials = $path && file_exists($path) &&
            filesize($path) > 0;
    }

    public function testBase64Audio()
    {
        $audioFile = __DIR__ . '/data/audio32KHz.raw';

        $base64Audio = require __DIR__ . '/../src/base64_encode_audio.php';

        $audioFileResource = fopen($audioFile, 'r');
        $this->assertEquals(base64_decode($base64Audio), stream_get_contents($audioFileResource));
    }

    /** @dataProvider provideTranscribe */
    public function testTranscribe($audioFile, $encoding, $sampleRate, $options = [])
    {
        if (!self::$hasCredentials) {
            $this->markTestSkipped('No application credentials were found.');
        }
        if (!self::$bucketName && 0 === strpos($audioFile, 'gs://')) {
            $this->markTestSkipped('You must set the GOOGLE_BUCKET_NAME environment variable.');
        }
        $output = $this->runCommand('transcribe', [
            'audio-file' => $audioFile,
            '--encoding' => $encoding,
            '--sample-rate' => $sampleRate,
        ] + $options);

        $this->assertContains('how old is the Brooklyn Bridge', $output);

        // Check for the word time offsets
        if (isset($options['--async'])) {
            $this->assertRegexp('/start: .*s, end: .*s/', $output);
        }
    }

    public function provideTranscribe()
    {
        self::$bucketName = getenv('GOOGLE_BUCKET_NAME');
        return [
            [__DIR__ . '/data/audio32KHz.raw', 'LINEAR16', '32000'],
            [__DIR__ . '/data/audio32KHz.flac', 'FLAC', '32000'],
            [__DIR__ . '/data/audio32KHz.raw', 'LINEAR16', '32000', ['--stream' => true]],
            [__DIR__ . '/data/audio32KHz.raw', 'LINEAR16', '32000', ['--async' => true]],
            ['gs://' . self::$bucketName . '/audio32KHz.raw', 'LINEAR16', '32000'],
            ['gs://' . self::$bucketName . '/audio32KHz.raw', 'LINEAR16', '32000', ['--async' => true]],
        ];
    }

    private function runCommand($commandName, $args = [])
    {
        $application = require __DIR__ . '/../speech.php';
        $command = $application->get($commandName);
        $commandTester = new CommandTester($command);

        ob_start();
        $commandTester->execute(
            $args,
            ['interactive' => false]);

        return ob_get_clean();
    }
}
