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

use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for vision commands.
 */
class textToSpeechTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!$creds = getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            $this->markTestSkipped('Set the GOOGLE_APPLICATION_CREDENTIALS ' .
                'environment variable');
        }
    }
    public function testListVoices()
    {
        $output = $this->runCommand('list_voices');
        $this->assertContains('en-US', $output);
        $this->assertContains('FEMALE', $output);
    }
    public function testSynthesizeSsml()
    {
        $output = $this->runCommand('synthesize_ssml', [
            'text' => '<speak>Hello there.</speak>'
        ]);
        $this->assertContains('Audio content written to', $output);
        $this->assertGreaterThan(0,filesize('output.mp3'));
    }
    public function testSynthesizeText()
    {
        $output = $this->runCommand('synthesize_text', [
            'text' => 'hello there'
        ]);
        $this->assertContains('Audio content written to', $output);
        $this->assertGreaterThan(0,filesize('output.mp3'));
    }
    public function testSynthesizeSsmlFile()
    {
        $path = __DIR__ . '/../resources/hello.ssml';
        $output = $this->runCommand('synthesize_ssml_file', [
            'path' => $path
        ]);
        $this->assertContains('Audio content written to', $output);
        $this->assertGreaterThan(0,filesize('output.mp3'));
    }
    public function testSynthesizeTextFile()
    {
        $path = __DIR__ . '/../resources/hello.txt';
        $output = $this->runCommand('synthesize_text_file', [
            'path' => $path
        ]);
        $this->assertContains('Audio content written to', $output);
        $this->assertGreaterThan(0,filesize('output.mp3'));
    }
    private function runCommand($commandName, array $args = [])
    {
        $application = require __DIR__ . '/../texttospeech.php';
        $command = $application->get($commandName);
        $commandTester = new CommandTester($command);
        ob_start();
        $commandTester->execute(
            $args,
            ['interactive' => false]);
        return ob_get_clean();
    }
}
