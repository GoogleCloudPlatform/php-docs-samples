<?php
/*
 * Copyright 2019 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/*
 * DO NOT EDIT! This is a generated sample ("Request",  "translate_v3_translate_text")
 */

// sample-metadata
//   title: Translating Text
//   description: Translating Text
//   usage: php v3_translate_text.php [--text "Hello, world!"] [--target_language fr] [--project_id "[Google Cloud Project ID]"]
// [START translate_v3_translate_text]
require_once __DIR__ . '/../vendor/autoload.php';

use Google\Cloud\Translate\V3\TranslationServiceClient;

/**
 * Translating Text.
 *
 * @param string $text           The content to translate in string format
 * @param string $targetLanguage Required. The BCP-47 language code to use for translation.
 */
function sampleTranslateText($text, $targetLanguage, $projectId)
{
    $translationServiceClient = new TranslationServiceClient();

    // $text = 'Hello, world!';
    // $targetLanguage = 'fr';
    // $projectId = '[Google Cloud Project ID]';
    $contents = [$text];
    $formattedParent = $translationServiceClient->locationName($projectId, 'global');

    try {
        $response = $translationServiceClient->translateText($contents, $targetLanguage, $formattedParent);
        // Display the translation for each input text provided
        foreach ($response->getTranslations() as $translation) {
            printf('Translated text: %s' . PHP_EOL, $translation->getTranslatedText());
        }
    } finally {
        $translationServiceClient->close();
    }
}
// [END translate_v3_translate_text]

$opts = [
    'text::',
    'target_language::',
    'project_id::',
];

$defaultOptions = [
    'text' => 'Hello, world!',
    'target_language' => 'fr',
    'project_id' => '[Google Cloud Project ID]',
];

$options = getopt('', $opts);
$options += $defaultOptions;

$text = $options['text'];
$targetLanguage = $options['target_language'];
$projectId = $options['project_id'];

sampleTranslateText($text, $targetLanguage, $projectId);
