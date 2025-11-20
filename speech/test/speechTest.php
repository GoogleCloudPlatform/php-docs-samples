<?php
/**
 * Copyright 2023 Google Inc.
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
    const GLOBAL = 'global';
    use TestTrait;

    private static string $recognizerId;

    public static function setUpBeforeClass(): void
    {
        self::$projectId = self::requireEnv('GOOGLE_CLOUD_PROJECT');
        self::$recognizerId = 'test-recognizer-' . uniqid();
    }

    public static function tearDownAfterClass(): void
    {
        self::runFunctionSnippet('delete_recognizer', [self::$projectId, self::GLOBAL, self::$recognizerId]);
    }

    public function testBase64Audio()
    {
        $audioFile = __DIR__ . '/data/audio32KHz.flac';

        $output = $this->runFunctionSnippet('base64_encode_audio', [$audioFile]);

        $audioFileResource = fopen($audioFile, 'r');
        $this->assertEquals(
            base64_decode($output),
            stream_get_contents($audioFileResource)
        );
    }

    public function testCreateRecognizer()
    {
        $output = $this->runFunctionSnippet('create_recognizer', [self::$projectId, self::GLOBAL, self::$recognizerId]);
        $this->assertStringContainsString('Created Recognizer:', $output);
    }

    /** @depends testCreateRecognizer */
    public function testTranscribeEnhanced()
    {
        $path = __DIR__ . '/data/commercial_mono.wav';
        $output = $this->runFunctionSnippet('transcribe_enhanced_model', [self::$projectId, self::GLOBAL, self::$recognizerId, $path]);
        $this->assertStringContainsString('Chrome', $output);
    }

    /** @depends testCreateRecognizer */
    public function testTranscribeModel()
    {
        $path = __DIR__ . '/data/audio32KHz.flac';
        $output = $this->runFunctionSnippet(
            'transcribe_model_selection',
            [self::$projectId, self::GLOBAL, self::$recognizerId, $path, 'telephony']
        );
        $this->assertStringContainsStringIgnoringCase(
            'how old is the Brooklyn Bridge',
            $output
        );
    }

    /** @depends testCreateRecognizer */
    public function testTranscribePunctuation()
    {
        $path = __DIR__ . '/data/audio32KHz.flac';
        $output = $this->runFunctionSnippet('transcribe_auto_punctuation', [self::$projectId, self::GLOBAL, self::$recognizerId, $path]);
        $this->assertStringContainsStringIgnoringCase(
            'How old is the Brooklyn Bridge',
            $output
        );
    }

    public function testTranscribeWords()
    {
        $recognizerId = self::$recognizerId . '-chirp3';
        $audioFile = 'gs://cloud-samples-data/speech/brooklyn_bridge.raw';
        $location = 'eu';

        $output = $this->runFunctionSnippet('create_recognizer', [self::$projectId, $location, $recognizerId, 'chirp_3']);
        $this->assertStringContainsString('Created Recognizer:', $output);

        $output = $this->runFunctionSnippet('transcribe_async_words', [self::$projectId, $location, $recognizerId, $audioFile]);

        // Check for the word time offsets
        $this->assertStringContainsString('Word: How (start: ', $output);
    }

    public function testTranscribeMultRegion()
    {
        $recognizerId = self::$recognizerId . '-eu';
        $audioFile = 'gs://cloud-samples-data/speech/brooklyn_bridge.raw';
        $location = 'eu';

        $output = $this->runFunctionSnippet('create_recognizer', [self::$projectId, $location, $recognizerId]);
        $this->assertStringContainsString('Created Recognizer:', $output);

        $output = $this->runFunctionSnippet('multi_region_gcs', [self::$projectId, $location, $recognizerId, $audioFile]);

        $this->assertStringContainsString('how old is the Brooklyn Bridge', $output);
    }

    /**
     * @dataProvider provideTranscribe
     *
     * @depends testCreateRecognizer
     */
    public function testTranscribe($command, $audioFile, $requireGrpc = false)
    {
        if ($requireGrpc && !extension_loaded('grpc')) {
            self::markTestSkipped('Must enable grpc extension.');
        }

        $output = $this->runFunctionSnippet($command, [self::$projectId, self::GLOBAL, self::$recognizerId, $audioFile]);

        $this->assertStringContainsString('old is the Brooklyn Bridge', $output);
    }

    public function provideTranscribe()
    {
        return [
            ['transcribe_sync', __DIR__ . '/data/audio32KHz.flac'],
            ['transcribe_sync_gcs', 'gs://cloud-samples-data/speech/audio.raw'],
            ['transcribe_async_gcs', 'gs://cloud-samples-data/speech/audio.raw'],
            ['profanity_filter_gcs', 'gs://cloud-samples-data/speech/brooklyn_bridge.raw'],
            ['profanity_filter', __DIR__ . '/data/audio32KHz.flac'],
            ['streaming_recognize', __DIR__ . '/data/audio32KHz.flac', true],
        ];
    }
}
