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

require_once __DIR__ . '/../src/create_recognizer.php';
require_once __DIR__ . '/../src/delete_recognizer.php';

class speechTest extends TestCase
{
    use TestTrait;

    private $recognizerId;
    private $projectId;

    protected function setUp(): void
    {
        $this->projectId = $this->requireEnv('GOOGLE_CLOUD_PROJECT');
        $this->recognizerId = 'test-recognizer-' . uniqid();
        create_recognizer($this->projectId, 'global', $this->recognizerId);
    }

    protected function tearDown(): void
    {
        delete_recognizer($this->projectId, 'global', $this->recognizerId);
    }

    public function testBase64Audio()
    {
        $audioFile = __DIR__ . '/data/audio32KHz.raw';

        $output = $this->runFunctionSnippet('base64_encode_audio', [$audioFile]);

        $audioFileResource = fopen($audioFile, 'r');
        $this->assertEquals(
            base64_decode($output),
            stream_get_contents($audioFileResource)
        );
    }

    public function testTranscribeEnhanced()
    {
        $path = __DIR__ . '/data/commercial_mono.wav';
        $output = $this->runFunctionSnippet('transcribe_enhanced_model', [$this->projectId, 'global', $this->recognizerId, $path]);
        $this->assertStringContainsString('Chrome', $output);
    }

    public function testTranscribeModel()
    {
        $path = __DIR__ . '/data/audio32KHz.raw';
        $output = $this->runFunctionSnippet(
            'transcribe_model_selection',
            [$this->projectId, 'global', $this->recognizerId, $path, 'video']
        );
        $this->assertStringContainsStringIgnoringCase(
            'how old is the Brooklyn Bridge',
            $output
        );
    }

    public function testTranscribePunctuation()
    {
        $path = __DIR__ . '/data/audio32KHz.raw';
        $output = $this->runFunctionSnippet('transcribe_auto_punctuation', [$this->projectId, 'global', $this->recognizerId, $path]);
        $this->assertStringContainsStringIgnoringCase(
            'How old is the Brooklyn Bridge',
            $output
        );
    }

    /** @dataProvider provideTranscribe */
    public function testTranscribe($command, $audioFile, $requireGrpc = false)
    {
        if ($requireGrpc && !extension_loaded('grpc')) {
            self::markTestSkipped('Must enable grpc extension.');
        }

        $output = $this->runFunctionSnippet($command, [$this->projectId, 'global', $this->recognizerId, $audioFile]);

        $this->assertStringContainsString('how old is the Brooklyn Bridge', $output);

        // Check for the word time offsets
        if (in_array($command, ['transcribe_async_words'])) {
            $this->assertMatchesRegularExpression('/start_offset {\\s*seconds: \\d+\\s*}/', $output);
            $this->assertMatchesRegularExpression('/end_offset {\\s*seconds: \\d+\\s*}/', $output);
        }
    }

    public function provideTranscribe()
    {
        return [
            ['transcribe_sync', __DIR__ . '/data/audio32KHz.raw'],
            ['transcribe_sync_gcs', 'gs://cloud-samples-data/speech/audio.raw'],
            ['transcribe_async', __DIR__ . '/data/audio32KHz.raw'],
            ['transcribe_async_gcs', 'gs://cloud-samples-data/speech/audio.raw'],
            ['transcribe_async_words', __DIR__ . '/data/audio32KHz.raw'],
            ['profanity_filter_gcs', 'gs://cloud-samples-data/speech/profanity.raw'],
            ['multi_region_gcs', 'gs://cloud-samples-data/speech/brooklyn_bridge.raw' ],
            ['profanity_filter', __DIR__ . '/data/profanity.raw'],
            ['streaming_recognize', __DIR__ . '/data/audio32KHz.raw', true],
        ];
    }
}
