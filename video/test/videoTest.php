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

    public function setUp()
    {
        $this->useResourceExhaustedBackoff();
    }

    public function testAnalyzeLabels()
    {
        $output = $this->runSnippet(
            'analyze_labels_gcs',
            [$this->gcsUri(), 10]
        );
        $this->assertContains('cat', $output);
        $this->assertContains('Video label description', $output);
        $this->assertContains('Shot label description', $output);
        $this->assertContains('Category', $output);
        $this->assertContains('Segment', $output);
        $this->assertContains('Shot', $output);
        $this->assertContains('Confidence', $output);
    }

    public function testAnalyzeLabelsFile()
    {
        $output = $this->runSnippet(
            'analyze_labels_file',
            [__DIR__ . '/data/cat_shortened.mp4', 10]
        );
        $this->assertContains('cat', $output);
        $this->assertContains('Video label description:', $output);
        $this->assertContains('Shot label description:', $output);
        $this->assertContains('Category:', $output);
        $this->assertContains('Segment:', $output);
        $this->assertContains('Shot:', $output);
        $this->assertContains('Confidence:', $output);
    }

    public function testAnalyzeExplicitContent()
    {
        $output = $this->runSnippet(
            'analyze_explicit_content',
            [$this->gcsUri(), 10]
        );
        $this->assertContains('pornography:', $output);
    }

    public function testAnalyzeShots()
    {
        $output = $this->runSnippet(
            'analyze_shots',
            [$this->gcsUri(), 10]
        );
        $this->assertContains('Shot:', $output);
        $this->assertContains(' to ', $output);
    }

    public function testTranscription()
    {
        $output = $this->runSnippet(
            'analyze_transcription',
            [$this->gcsUriTwo(), 10]
        );
        $this->assertContains('Transcript:', $output);
        $this->assertContains('Paris', $output);
        $this->assertContains('France', $output);
    }

    public function testAnalyzeTextDetection()
    {
        $output = $this->runSnippet(
            'analyze_text_detection',
            [$this->gcsUriTwo(), 10]
        );
        $this->assertContains('GOOGLE', $output);
        $this->assertContains('Video text description:', $output);
        $this->assertContains('Segment:', $output);
        $this->assertContains('Confidence:', $output);
    }

    public function testAnalyzeTextDetectionFile()
    {
        $output = $this->runSnippet(
            'analyze_text_detection_file',
            [__DIR__ . '/data/googlework_short.mp4', 10]
        );
        $this->assertContains('GOOGLE', $output);
        $this->assertContains('Video text description:', $output);
        $this->assertContains('Segment:', $output);
        $this->assertContains('Confidence:', $output);
    }

    public function testObjectTracking()
    {
        $output = $this->runSnippet(
            'analyze_object_tracking',
            [$this->gcsUriTwo(), 10]
        );
        $this->assertContains('/m/01g317', $output);
        $this->assertContains('person', $output);
    }

    public function testObjectTrackingFile()
    {
        $output = $this->runSnippet(
            'analyze_object_tracking_file',
            [__DIR__ . '/data/googlework_short.mp4', 10]
        );
        $this->assertContains('/m/01g317', $output);
        $this->assertContains('person', $output);
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
