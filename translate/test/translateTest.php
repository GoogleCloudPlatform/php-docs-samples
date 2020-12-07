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

    private static $bucket;

    public static function setUpBeforeClass()
    {
        self::checkProjectEnvVars();
        self::$bucket = (new StorageClient())->createBucket(
            sprintf('%s-translate-test-bucket-%s', self::$projectId, rand())
        );
    }

    public static function tearDownAfterClass()
    {
        foreach (self::$bucket->objects() as $object) {
            $object->delete();
        }

        self::$bucket->delete();
    }

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

    public function testV3TranslateText()
    {
        $output = $this->runSnippet(
            'v3_translate_text',
            [
                'Hello world',
                'sr-Latn',
                self::$projectId
            ]
        );
        $option1 = 'Zdravo svet';
        $option2 = 'Pozdrav svijetu';
        $option3 = 'Zdravo svijete';
        $this->assertThat($output,
            $this->logicalOr(
                $this->stringContains($option1),
                $this->stringContains($option2),
                $this->stringContains($option3)
            )
        );
    }

    public function testV3TranslateTextWithGlossaryAndModel()
    {
        $glossaryId = sprintf('please-delete-me-%d', rand());
        $this->runSnippet(
            'v3_create_glossary',
            [
                self::$projectId,
                $glossaryId,
                'gs://cloud-samples-data/translation/glossary_ja.csv'
            ]
        );
        $output = $this->runSnippet(
            'v3_translate_text_with_glossary_and_model',
            [
                'TRL3089491334608715776',
                $glossaryId,
                'That\' il do it. deception',
                'ja',
                'en',
                self::$projectId,
                'us-central1'
            ]
        );
        $this->assertContains('欺く', $output);
        $this->assertContains('やる', $output);
        $this->runSnippet(
            'v3_delete_glossary',
            [
                self::$projectId,
                $glossaryId
            ]
        );
    }

    public function testV3TranslateTextWithGlossary()
    {
        $glossaryId = sprintf('please-delete-me-%d', rand());
        $this->runSnippet(
            'v3_create_glossary',
            [
                self::$projectId,
                $glossaryId,
                'gs://cloud-samples-data/translation/glossary_ja.csv'
            ]
        );
        $output = $this->runSnippet(
            'v3_translate_text_with_glossary',
            [
                'account',
                'en',
                'ja',
                self::$projectId,
                $glossaryId
            ]
        );
        $option1 = 'アカウント';
        $option2 = '口座';
        $this->assertThat($output,
            $this->logicalOr(
                $this->stringContains($option1),
                $this->stringContains($option2)
            )
        );
        $this->runSnippet(
            'v3_delete_glossary',
            [
                self::$projectId,
                $glossaryId
            ]
        );
    }

    public function testV3TranslateTextWithModel()
    {
        $output = $this->runSnippet(
            'v3_translate_text_with_model',
            [
                'TRL3089491334608715776',
                'That\' il do it.',
                'ja',
                'en',
                self::$projectId,
                'us-central1'
            ]
        );
        $this->assertContains('やる', $output);
    }

    public function testV3CreateListGetDeleteGlossary()
    {
        $glossaryId = sprintf('please-delete-me-%d', rand());
        $output = $this->runSnippet(
            'v3_create_glossary',
            [
                self::$projectId,
                $glossaryId,
                'gs://cloud-samples-data/translation/glossary_ja.csv'
            ]
        );
        $this->assertContains('Created', $output);
        $this->assertContains($glossaryId, $output);
        $this->assertContains(
            'gs://cloud-samples-data/translation/glossary_ja.csv',
            $output
        );
        $output = $this->runSnippet(
            'v3_list_glossary',
            [self::$projectId]
        );
        $this->assertContains($glossaryId, $output);
        $this->assertContains(
            'gs://cloud-samples-data/translation/glossary_ja.csv',
            $output
        );
        $output = $this->runSnippet(
            'v3_get_glossary',
            [
                self::$projectId,
                $glossaryId
            ]
        );
        $this->assertContains($glossaryId, $output);
        $this->assertContains(
            'gs://cloud-samples-data/translation/glossary_ja.csv',
            $output
        );
        $output = $this->runSnippet(
            'v3_delete_glossary',
            [
                self::$projectId,
                $glossaryId
            ]
        );
        $this->assertContains('Deleted', $output);
    }

    public function testV3ListLanguagesWithTarget()
    {
        $output = $this->runSnippet(
            'v3_get_supported_languages_for_target',
            [
                'is',
                self::$projectId
            ]
        );
        $this->assertContains('Language Code: sq', $output);
        $this->assertContains('Display Name: albanska', $output);
    }

    public function testV3ListLanguages()
    {
        $output = $this->runSnippet(
            'v3_get_supported_languages',
            [self::$projectId]
        );
        $this->assertContains('zh-CN', $output);
    }

    public function testV3DetectLanguage()
    {
        $output = $this->runSnippet(
            'v3_detect_language',
            [
                'Hæ sæta',
                self::$projectId
            ]
        );
        $this->assertContains('is', $output);
    }

    public function testV3BatchTranslateText()
    {
        $outputUri = sprintf(
            'gs://%s/%d/',
            self::$bucket->name(),
            rand()
        );
        $output = $this->runSnippet(
            'v3_batch_translate_text',
            [
                'gs://cloud-samples-data/translation/text.txt',
                $outputUri,
                self::$projectId,
                'us-central1',
                'en',
                'es'
            ]
        );
        $this->assertContains('Total Characters: 13', $output);
    }

    public function testV3BatchTranslateTextWithGlossaryAndModel()
    {
        $outputUri = sprintf(
            'gs://%s/%d/',
            self::$bucket->name(),
            rand()
        );
        $glossaryId = sprintf('please-delete-me-%d', rand());
        $this->runSnippet(
            'v3_create_glossary',
            [
                self::$projectId,
                $glossaryId,
                'gs://cloud-samples-data/translation/glossary_ja.csv'
            ]
        );
        $output = $this->runSnippet(
            'v3_batch_translate_text_with_glossary_and_model',
            [
                'gs://cloud-samples-data/translation/text_with_custom_model_and_glossary.txt',
                $outputUri,
                self::$projectId,
                'us-central1',
                'ja',
                'en',
                'TRL3089491334608715776',
                $glossaryId
            ]
        );
        $this->runSnippet(
            'v3_delete_glossary',
            [
                self::$projectId,
                $glossaryId
            ]
        );
        $this->assertContains('Total Characters: 25', $output);
    }

    public function testV3BatchTranslateTextWithGlossary()
    {
        $outputUri = sprintf(
            'gs://%s/%d/',
            self::$bucket->name(),
            rand()
        );
        $glossaryId = sprintf('please-delete-me-%d', rand());
        $this->runSnippet(
            'v3_create_glossary',
            [
                self::$projectId,
                $glossaryId,
                'gs://cloud-samples-data/translation/glossary_ja.csv'
            ]
        );
        $output = $this->runSnippet(
            'v3_batch_translate_text_with_glossary',
            [
                'gs://cloud-samples-data/translation/text_with_glossary.txt',
                $outputUri,
                self::$projectId,
                'us-central1',
                $glossaryId,
                'ja',
                'en'
            ]
        );
        $this->runSnippet(
            'v3_delete_glossary',
            [
                self::$projectId,
                $glossaryId
            ]
        );
        $this->assertContains('Total Characters: 9', $output);
    }

    public function testV3BatchTranslateTextWithModel()
    {
        $outputUri = sprintf(
            'gs://%s/%d/',
            self::$bucket->name(),
            rand()
        );
        $output = $this->runSnippet(
            'v3_batch_translate_text_with_model',
            [
                'gs://cloud-samples-data/translation/custom_model_text.txt',
                $outputUri,
                self::$projectId,
                'us-central1',
                'ja',
                'en',
                'TRL3089491334608715776'
            ]
        );
        $this->assertContains('Total Characters: 15', $output);
    }
}
