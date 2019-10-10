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
use PHPUnit\Framework\TestCase;

class speechTest extends TestCase
{
    use TestTrait;

    protected static $bucketName;

    public function testBase64Audio()
    {
        $audioFile = __DIR__ . '/data/audio32KHz.raw';

        $output = $this->runSnippet('base64_encode_audio', [$audioFile]);

        $audioFileResource = fopen($audioFile, 'r');
        $this->assertEquals(
            base64_decode($output),
            stream_get_contents($audioFileResource)
        );
    }

    public function testTranscribeEnhanced()
    {
        $path = __DIR__ . '/data/commercial_mono.wav';
        $output = $this->runSnippet('transcribe_enhanced_model', [$path]);
        $this->assertContains('Chrome', $output);
    }

    public function testTranscribeModel()
    {
        $path = __DIR__ . '/data/audio32KHz.raw';
        $output = $this->runSnippet(
            'transcribe_model_selection',
            [$path, 'video']
        );
        // $this->assertContains('the weather outside is sunny',$output);
        $this->assertContains('how old is the Brooklyn Bridge', $output);
    }

    public function testTranscribePunctuation()
    {
        $path = __DIR__ . '/data/audio32KHz.raw';
        $output = $this->runSnippet('transcribe_auto_punctuation', [$path]);
        $this->assertContains('How old is the Brooklyn Bridge', $output);
    }

    /** @dataProvider provideTranscribe */
    public function testTranscribe($command, $audioFile, $requireGrpc = false)
    {
        if ($requireGrpc && !extension_loaded('grpc')) {
            self::markTestSkipped('Must enable grpc extension.');
        }
        if (!self::$bucketName && in_array($command, ['transcribe_gcs', 'transcribe_async_gcs'])) {
            $this->requireEnv('GOOGLE_STORAGE_BUCKET');
        }
        $output = $this->runSnippet($command, [$audioFile]);

        $this->assertContains('how old is the Brooklyn Bridge', $output);

        // Check for the word time offsets
        if (in_array($command, ['transcribe_async-words'])) {
            $this->assertRegexp('/start: "*.*s", end: "*.*s/', $output);
        }
    }

    public function provideTranscribe()
    {
        self::$bucketName = getenv('GOOGLE_STORAGE_BUCKET');
        return [
            ['transcribe_sync', __DIR__ . '/data/audio32KHz.raw'],
            ['transcribe_sync_gcs', 'gs://' . self::$bucketName . '/speech/audio32KHz.raw'],
            ['transcribe_async', __DIR__ . '/data/audio32KHz.raw'],
            ['transcribe_async_gcs', 'gs://' . self::$bucketName . '/speech/audio32KHz.raw'],
            ['transcribe_async_words', __DIR__ . '/data/audio32KHz.raw'],
            ['streaming_recognize', __DIR__ . '/data/audio32KHz.raw', true],
        ];
    }
}
