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


namespace Google\Cloud\Samples\Translate;

use Symfony\Component\Console\Tester\CommandTester;

/**
 * Unit Tests for transcribe commands.
 */
class CommandTest extends \PHPUnit_Framework_TestCase
{
    private $application;

    public function setUp()
    {
        $this->application = require __DIR__ . '/../translate.php';
    }

    public function testTranslate()
    {
        $output = $this->runCommand('translate', [
            'text' => 'Hello.',
            '-t' => 'ja',
        ]);
        $this->assertContains('Source language: en', $output);
        $this->assertContains('Translation:', $output);
    }

    /** @expectedException Google\Cloud\Core\Exception\BadRequestException */
    public function testTranslateBadLanguage()
    {
        $this->runCommand('translate', [
            'text' => 'Hello.',
            '-t' => 'jp',
        ]);
    }

    public function testTranslateWithModel()
    {
        $output = $this->runCommand('translate', [
            'text' => 'Hello.',
            '-t' => 'ja',
            '--model' => 'nmt',
        ]);
        $this->assertContains('Source language: en', $output);
        $this->assertContains('Translation:', $output);
        $this->assertContains('Model: nmt', $output);
    }

    public function testDetectLanguage()
    {
        $output = $this->runCommand('detect-language', [
            'text' => 'Hello.',
        ]);
        $this->assertContains('Language code: en', $output);
        $this->assertContains('Confidence:', $output);
    }

    public function testListCodes()
    {
        $output = $this->runCommand('list-codes');
        $this->assertContains("\nen\n", $output);
        $this->assertContains("\nja\n", $output);
    }

    public function testListLanguagesInEnglish()
    {
        $output = $this->runCommand('list-langs', [
            '-t' => 'en'
        ]);
        $this->assertContains('ja: Japanese', $output);
    }

    public function testListLanguagesInJapanese()
    {
        $output = $this->runCommand('list-langs', [
            '-t' => 'ja'
        ]);
        $this->assertContains('en: 英語', $output);
    }

    private function runCommand($commandName, $args = [])
    {
        $command = $this->application->get($commandName);
        $commandTester = new CommandTester($command);

        try {
            ob_start();
            $commandTester->execute(
                $args,
                ['interactive' => false]
            );
        } finally {
            $output = ob_get_clean();
        }
        return $output;
    }
}
