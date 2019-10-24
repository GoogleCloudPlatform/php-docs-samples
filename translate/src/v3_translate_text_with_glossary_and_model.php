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
 * DO NOT EDIT! This is a generated sample ("Request",  "translate_v3_translate_text_with_glossary_and_model")
 */

// sample-metadata
//   title: Translating Text with Glossary and Model
//   description: Translating Text with Glossary and Model
//   usage: php v3_translate_text_with_glossary.php [--model_id "[MODEL ID]"] [--glossary_id "projects/[YOUR_PROJECT_ID]/locations/[LOCATION]/glossaries/[YOUR_GLOSSARY_ID]"] [--text "Hello, world!"] [--target_language fr] [--source_language en] [--project_id "[Google Cloud Project ID]"] [--location global]
// [START translate_v3_translate_text_with_glossary_and_model]
require_once __DIR__ . '/../vendor/autoload.php';

use Google\Cloud\Translate\V3\TranslationServiceClient;
use Google\Cloud\Translate\V3\TranslateTextGlossaryConfig;

/**
 * Translating Text with Glossary and Model.
 *
 * @param string $modelId      The `model` type requested for this translation.
 * @param string $glossaryId   Specifies the glossary used for this translation.
 * @param string $text           The content to translate in string format
 * @param string $targetLanguage Required. The BCP-47 language code to use for translation.
 * @param string $sourceLanguage Optional. The BCP-47 language code of the input text.
 */
function sampleTranslateTextWithGlossaryAndModel($modelId, $glossaryId, $text, $targetLanguage, $sourceLanguage, $projectId, $location)
{
    $translationServiceClient = new TranslationServiceClient();

    // $modelId = '[MODEL ID]';
    // $glossaryId = '[YOUR_GLOSSARY_ID]';
    // $text = 'Hello, world!';
    // $targetLanguage = 'fr';
    // $sourceLanguage = 'en';
    // $projectId = '[Google Cloud Project ID]';
    // $location = 'global';
    $glossaryPath = $translationServiceClient->glossaryName($projectId, $location, $glossaryId);
    $modelPath = sprintf('projects/%s/locations/%s/models/%s', $projectId, $location, $modelId);
    $contents = [$text];
    $glossaryConfig = new TranslateTextGlossaryConfig();
    $glossaryConfig->setGlossary($glossaryPath);
    $formattedParent = $translationServiceClient->locationName($projectId, $location);

    // Optional. Can be "text/plain" or "text/html".
    $mimeType = 'text/plain';

    try {
        $response = $translationServiceClient->translateText($contents, $targetLanguage, $formattedParent, ['model' => $modelPath, 'glossaryConfig' => $glossaryConfig, 'sourceLanguageCode' => $sourceLanguage, 'mimeType' => $mimeType]);
        // Display the translation for each input text provided
        foreach ($response->getGlossaryTranslations() as $translation) {
            printf('Translated text: %s' . PHP_EOL, $translation->getTranslatedText());
        }
    } finally {
        $translationServiceClient->close();
    }
}
// [END translate_v3_translate_text_with_glossary_and_model]

$opts = [
    'model_id::',
    'glossary_id::',
    'text::',
    'target_language::',
    'source_language::',
    'project_id::',
    'location::',
];

$defaultOptions = [
    'model_id' => '[MODEL ID]',
    'glossary_id' => '[YOUR_GLOSSARY_ID]',
    'text' => 'Hello, world!',
    'target_language' => 'fr',
    'source_language' => 'en',
    'project_id' => '[Google Cloud Project ID]',
    'location' => 'global',
];

$options = getopt('', $opts);
$options += $defaultOptions;

$modelId = $options['model_id'];
$glossaryId = $options['glossary_id'];
$text = $options['text'];
$targetLanguage = $options['target_language'];
$sourceLanguage = $options['source_language'];
$projectId = $options['project_id'];
$location = $options['location'];

sampleTranslateTextWithGlossaryAndModel($modelId, $glossaryId, $text, $targetLanguage, $sourceLanguage, $projectId, $location);
