<?php
/**
 * Copyright 2018 Google Inc.
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
namespace Google\Cloud\Samples\TextToSpeech;

use Google\Cloud\TestUtils\TestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Unit Tests for vision commands.
 */
class textToSpeechTest extends TestCase
{
    use TestTrait;

    public function testListVoices()
    {
        $output = $this->runSnippet('list_voices');
        $this->assertContains('en-US', $output);
        $this->assertContains('FEMALE', $output);
    }

    public function testSynthesizeSsml()
    {
        $output = $this->runSnippet(
            'synthesize_ssml',
            ['<speak>Hello there.</speak>']
        );
        $this->assertContains('Audio content written to', $output);
        $this->assertGreaterThan(0, filesize('output.mp3'));
        unlink('output.mp3');
    }

    public function testSynthesizeText()
    {
        $output = $this->runSnippet('synthesize_text', ['hello there']);

        $this->assertContains('Audio content written to', $output);
        $this->assertGreaterThan(0, filesize('output.mp3'));
        unlink('output.mp3');
    }

    public function testSynthesizeTextEffectsProfile()
    {
        $output = $this->runSnippet(
            'synthesize_text_effects_profile',
            ['hello there', 'telephony-class-application']
        );
        $this->assertContains('Audio content written to', $output);
        $this->assertGreaterThan(0, filesize('output.mp3'));
        unlink('output.mp3');
    }

    public function testSynthesizeSsmlFile()
    {
        $path = __DIR__ . '/../resources/hello.ssml';
        $output = $this->runSnippet('synthesize_ssml_file', [$path]);

        $this->assertContains('Audio content written to', $output);
        $this->assertGreaterThan(0, filesize('output.mp3'));
        unlink('output.mp3');
    }

    public function testSynthesizeTextFile()
    {
        $path = __DIR__ . '/../resources/hello.txt';
        $output = $this->runSnippet('synthesize_text_file', [$path]);

        $this->assertContains('Audio content written to', $output);
        $this->assertGreaterThan(0, filesize('output.mp3'));
        unlink('output.mp3');
    }

    public function testSynthesizeTextEffectsProfileFile()
    {
        $path = __DIR__ . '/../resources/hello.txt';
        $output = $this->runSnippet(
            'synthesize_text_effects_profile_file',
            [$path, 'telephony-class-application']
        );
        $this->assertContains('Audio content written to', $output);
        $this->assertGreaterThan(0, filesize('output.mp3'));
        unlink('output.mp3');
    }
}
