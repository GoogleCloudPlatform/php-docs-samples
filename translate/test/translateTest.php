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
use Google\Cloud\Storage\StorageClient;

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

    public function testV3TranslateText()
    {
        $output = $this->runSnippet('v3_translate_text', ['Hello world', 'sr-Latn', self::$projectId]);
        
        $option1 = "Zdravo svet";
        $option2 = "Pozdrav svijetu";
        $this->assertThat($output,
            $this->logicalOr(
                $this->stringContains($option1),
                $this->stringContains($option2)
            )
        );
    }

    // public function testV3TranslateTextWithGlossaryAndModel()
    // {
    //     $output = $this->runSnippet('v3_translate_text_with_glossary_and_model', ['ja']);
    //     $this->assertContains('en: 英語', $output);
    // }

    // public function testV3TranslateTextWithGlossary()
    // {
    //     $output = $this->runSnippet('v3_translate_text_with_glossary', ['account', 'en', 'ja', self::$projectId, glossary]);
    //     $this->assertContains('en: 英語', $output);
    // }

    // public function testV3TranslateTextWithModel()
    // {
    //     $output = $this->runSnippet('v3_translate_text_with_model', ['ja']);
    //     $this->assertContains('en: 英語', $output);
    // }

    public function testV3CreateListGetDeleteGlossary()
    {
        $output = $this->runSnippet('v3_create_glossary', ['Hello.']);
        $output = $this->runSnippet('v3_delete_glossary', ['Hello.']);
        $output = $this->runSnippet('v3_list_glossary');
        $output = $this->runSnippet('v3_get_glossary');
        $this->assertContains("\nen\n", $output);
        $this->assertContains("\nja\n", $output);
    }

    public function testV3ListLanguagesWithTarget()
    {
        $output = $this->runSnippet('v3_get_supported_languages_for_target', ['is', self::$projectId]);
        $this->assertContains("Language Code: sq", $output);
        $this->assertContains("Display Name: albanska", $output);
    }

    public function testV3ListLanguages()
    {
        $output = $this->runSnippet('v3_get_supported_languages', [self::$projectId]);
        $this->assertContains("zh-CN", $output);
    }

    public function testV3DetectLanguage()
    {
        $output = $this->runSnippet('v3_detect_language', ['Hæ sæta', self::$projectId]);
        $this->assertContains('is', $output);
    }

    public function testV3BatchTranslateText()
    {
        $storage = new StorageClient();
        $bucket = $storage->createBucket('who-lives-in-a-pineapple');
        $output = $this->runSnippet('v3_batch_translate_text', ['gs://cloud-samples-data/translation/text.txt', sprintf('gs://%s/under-the-sea/', $bucket->name()), self::$projectId, 'us-central1', 'en', 'es']);
        $bucket->delete();
        $this->assertContains('Total Characters: 13', $output);
    }

    // public function testV3BatchTranslateTextWithGlossaryAndModel()
    // {
    //     $output = $this->runSnippet('v3_batch_translate_text_with_glossary_and_model', ['ja']);
    //     $this->assertContains('en: 英語', $output);
    // }

    // public function testV3BatchTranslateTextWithGlossary()
    // {
    //     $output = $this->runSnippet('v3_batch_translate_text_with_glossary', ['ja']);
    //     $this->assertContains('en: 英語', $output);
    // }

    // public function testV3BatchTranslateTextWithModel()
    // {
    //     $output = $this->runSnippet('v3_batch_translate_text_with_model', ['ja']);
    //     $this->assertContains('en: 英語', $output);
    // }
}
