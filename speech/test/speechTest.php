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

use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\ExecuteCommandTrait;
use PHPUnit\Framework\TestCase;

class speechTest extends TestCase
{
    use TestTrait;
    use ExecuteCommandTrait;

    protected static $commandFile = __DIR__ . '/../speech.php';
    protected static $bucketName;

    public function testBase64Audio()
    {
        $audioFile = __DIR__ . '/data/audio32KHz.raw';

        $base64Audio = require __DIR__ . '/../src/base64_encode_audio.php';

        $audioFileResource = fopen($audioFile, 'r');
        $this->assertEquals(base64_decode($base64Audio), stream_get_contents($audioFileResource));
    }

    public function testTranscribeEnhanced()
    {
        $path = __DIR__ . '/data/commercial_mono.wav';
        $output = $this->runCommand('transcribe-enhanced', [
            'audio-file' => $path
        ]);
        $this->assertContains('Chrome',$output);
    }

    public function testTranscribeModel()
    {
        $path = __DIR__ . '/data/audio32KHz.raw';
        $output = $this->runCommand('transcribe-model', [
            'audio-file' => $path,
            '--model' => 'video'
        ]);
        // $this->assertContains('the weather outside is sunny',$output);
        $this->assertContains('how old is the Brooklyn Bridge',$output);
    }

    public function testTranscribePunctuation()
    {
        $path = __DIR__ . '/data/audio32KHz.raw';
        $output = $this->runCommand('transcribe-punctuation', [
            'audio-file' => $path
        ]);
        $this->assertContains('How old is the Brooklyn Bridge?',$output);
    }

    /** @dataProvider provideTranscribe */
    public function testTranscribe($command, $audioFile, $requireGrpc = false)
    {
        if ($requireGrpc && !extension_loaded('grpc')) {
            self::markTestSkipped('Must enable grpc extension.');
        }
        if (!self::$bucketName && in_array($command, ['transcribe-gcs', 'transcribe-async-gcs'])) {
            $this->requireEnv('GOOGLE_STORAGE_BUCKET');
        }
        $output = $this->runCommand($command, [
            'audio-file' => $audioFile
        ]);

        $this->assertContains('how old is the Brooklyn Bridge', $output);

        // Check for the word time offsets
        if (in_array($command, ['transcribe-async-words'])) {
            $this->assertRegexp('/start: "*.*s", end: "*.*s/', $output);
        }
    }

    public function provideTranscribe()
    {
        self::$bucketName = getenv('GOOGLE_STORAGE_BUCKET');
        return [
            ['transcribe', __DIR__ . '/data/audio32KHz.raw'],
            ['transcribe-gcs', 'gs://' . self::$bucketName . '/speech/audio32KHz.raw'],
            ['transcribe-async', __DIR__ . '/data/audio32KHz.raw'],
            ['transcribe-async-gcs', 'gs://' . self::$bucketName . '/speech/audio32KHz.raw'],
            ['transcribe-async-words', __DIR__ . '/data/audio32KHz.raw'],
            ['transcribe-stream', __DIR__ . '/data/audio32KHz.raw', true],
        ];
    }
}
