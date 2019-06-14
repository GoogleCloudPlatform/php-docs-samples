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

use PHPUnit\Framework\TestCase;
use Google\Cloud\TestUtils\TestTrait;

/**
 * Unit Tests for transcribe commands.
 */
class translateTest extends TestCase
{
    use TestTrait;

    public function testTranslate()
    {
        $output = $this->runSnippet(
            'translate',
            ['Hello.', 'ja']
        );
        $this->assertContains('Source language: en', $output);
        $this->assertContains('Translation:', $output);
    }

    /**
     * @expectedException Google\Cloud\Core\Exception\BadRequestException
     */
    public function testTranslateBadLanguage()
    {
        $this->runSnippet('translate', ['Hello.', 'jp']);
    }

    public function testTranslateWithModel()
    {
        $output = $this->runSnippet('translate_with_model', ['Hello.', 'ja']);
        $this->assertContains('Source language: en', $output);
        $this->assertContains('Translation:', $output);
        $this->assertContains('Model: nmt', $output);
    }

    public function testDetectLanguage()
    {
        $output = $this->runSnippet('detect_language', ['Hello.']);
        $this->assertContains('Language code: en', $output);
        $this->assertContains('Confidence:', $output);
    }

    public function testListCodes()
    {
        $output = $this->runSnippet('list_codes');
        $this->assertContains("\nen\n", $output);
        $this->assertContains("\nja\n", $output);
    }

    public function testListLanguagesInEnglish()
    {
        $output = $this->runSnippet('list_languages', ['en']);
        $this->assertContains('ja: Japanese', $output);
    }

    public function testListLanguagesInJapanese()
    {
        $output = $this->runSnippet('list_languages', ['ja']);
        $this->assertContains('en: 英語', $output);
    }
}
