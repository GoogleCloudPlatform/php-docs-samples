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
namespace Google\Cloud\Samples\VideoIntelligence;

use PHPUnit\Framework\TestCase;
use Google\Cloud\TestUtils\TestTrait;
use Google\Cloud\TestUtils\ExponentialBackoffTrait;

/**
 * Unit Tests for video commands.
 */
class videoTest extends TestCase
{
    use TestTrait;
    use ExponentialBackoffTrait;

    public function setUp(): void
    {
        $this->useResourceExhaustedBackoff();
    }

    public function testAnalyzeLabels()
    {
        $output = $this->runFunctionSnippet(
            'analyze_labels_gcs',
            ['uri' => $this->gcsUri(), 'pollingIntervalSeconds' => 10]
        );
        $this->assertStringContainsString('cat', $output);
        $this->assertStringContainsString('Video label description', $output);
        $this->assertStringContainsString('Shot label description', $output);
        $this->assertStringContainsString('Category', $output);
        $this->assertStringContainsString('Segment', $output);
        $this->assertStringContainsString('Shot', $output);
        $this->assertStringContainsString('Confidence', $output);
    }

    public function testAnalyzeLabelsFile()
    {
        $output = $this->runFunctionSnippet(
            'analyze_labels_file',
            ['path' => __DIR__ . '/data/cat_shortened.mp4', 'pollingIntervalSeconds' => 10]
        );
        $this->assertStringContainsString('cat', $output);
        $this->assertStringContainsString('Video label description:', $output);
        $this->assertStringContainsString('Shot label description:', $output);
        $this->assertStringContainsString('Category:', $output);
        $this->assertStringContainsString('Segment:', $output);
        $this->assertStringContainsString('Shot:', $output);
        $this->assertStringContainsString('Confidence:', $output);
    }

    public function testAnalyzeExplicitContent()
    {
        $output = $this->runFunctionSnippet(
            'analyze_explicit_content',
            ['uri' => $this->gcsUri(), 'pollingIntervalSeconds' => 10]
        );
        $this->assertStringContainsString('pornography:', $output);
    }

    public function testAnalyzeShots()
    {
        $output = $this->runFunctionSnippet(
            'analyze_shots',
            ['uri' => $this->gcsUri(), 'pollingIntervalSeconds' => 10]
        );
        $this->assertStringContainsString('Shot:', $output);
        $this->assertStringContainsString(' to ', $output);
    }

    public function testTranscription()
    {
        $output = $this->runFunctionSnippet(
            'analyze_transcription',
            ['uri' => $this->gcsUriTwo(), 'pollingIntervalSeconds' => 10]
        );
        $this->assertStringContainsString('Transcript:', $output);
        $this->assertStringContainsString('Paris', $output);
        $this->assertStringContainsString('France', $output);
    }

    public function testAnalyzeTextDetection()
    {
        $output = $this->runFunctionSnippet(
            'analyze_text_detection',
            ['uri' => $this->gcsUriTwo(), 'pollingIntervalSeconds' => 10]
        );
        $this->assertStringContainsString('GOOGLE', $output);
        $this->assertStringContainsString('Video text description:', $output);
        $this->assertStringContainsString('Segment:', $output);
        $this->assertStringContainsString('Confidence:', $output);
    }

    public function testAnalyzeTextDetectionFile()
    {
        $output = $this->runFunctionSnippet(
            'analyze_text_detection_file',
            ['path' => __DIR__ . '/data/googlework_short.mp4', 'pollingIntervalSeconds' => 10]
        );
        $this->assertStringContainsString('GOOGLE', $output);
        $this->assertStringContainsString('Video text description:', $output);
        $this->assertStringContainsString('Segment:', $output);
        $this->assertStringContainsString('Confidence:', $output);
    }

    public function testObjectTracking()
    {
        $output = $this->runFunctionSnippet(
            'analyze_object_tracking',
            ['uri' => $this->gcsUriTwo(), 'pollingIntervalSeconds' => 10]
        );
        $this->assertStringContainsString('/m/01g317', $output);
        $this->assertStringContainsString('person', $output);
    }

    public function testObjectTrackingFile()
    {
        $output = $this->runFunctionSnippet(
            'analyze_object_tracking_file',
            ['path' => __DIR__ . '/data/googlework_short.mp4', 'pollingIntervalSeconds' => 10]
        );
        $this->assertStringContainsString('/m/01g317', $output);
        $this->assertStringContainsString('person', $output);
    }

    private function gcsUri()
    {
        return sprintf(
            'gs://%s/video/cat_shortened.mp4',
            $this->requireEnv('GOOGLE_STORAGE_BUCKET')
        );
    }

    private function gcsUriTwo()
    {
        return sprintf(
            'gs://%s/video/googlework_short.mp4',
            $this->requireEnv('GOOGLE_STORAGE_BUCKET')
        );
    }
}
