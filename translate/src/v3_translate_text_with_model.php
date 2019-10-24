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
 * DO NOT EDIT! This is a generated sample ("Request",  "translate_v3_translate_text_with_model")
 */

// sample-metadata
//   title: Translating Text with Model
//   description: Translating Text with Model
//   usage: php v3_translate_text_with_model.php [--model_id "projects/[PROJECT ID]/locations/[LOCATION ID]/models/[MODEL ID]"] [--text "Hello, world!"] [--target_language fr] [--source_language en] [--project_id "[Google Cloud Project ID]"] [--location global]
// [START translate_v3_translate_text_with_model]
require_once __DIR__ . '/../vendor/autoload.php';

use Google\Cloud\Translate\V3\TranslationServiceClient;

/**
 * Translating Text with Model.
 *
 * @param string $modelPath      The `model` type requested for this translation.
 * @param string $text           The content to translate in string format
 * @param string $targetLanguage Required. The BCP-47 language code to use for translation.
 * @param string $sourceLanguage Optional. The BCP-47 language code of the input text.
 */
function sampleTranslateTextWithModel($modelId, $text, $targetLanguage, $sourceLanguage, $projectId, $location)
{
    $translationServiceClient = new TranslationServiceClient();

    // $modelId = '[MODEL ID]';
    // $text = 'Hello, world!';
    // $targetLanguage = 'fr';
    // $sourceLanguage = 'en';
    // $projectId = '[Google Cloud Project ID]';
    // $location = 'global';
    $modelPath = sprintf('projects/%s/locations/%s/models/%s', $projectId, $location ,$modelId)
    $contents = [$text];
    $formattedParent = $translationServiceClient->locationName($projectId, $location);

    // Optional. Can be "text/plain" or "text/html".
    $mimeType = 'text/plain';

    try {
        $response = $translationServiceClient->translateText($contents, $targetLanguage, ['model' => $modelPath, 'sourceLanguageCode' => $sourceLanguage, 'parent' => $formattedParent, 'mimeType' => $mimeType]);
        // Display the translation for each input text provided
        foreach ($response->getTranslations() as $translation) {
            printf('Translated text: %s' . PHP_EOL, $translation->getTranslatedText());
        }
    } finally {
        $translationServiceClient->close();
    }
}
// [END translate_v3_translate_text_with_model]

$opts = [
    'model_id::',
    'text::',
    'target_language::',
    'source_language::',
    'project_id::',
    'location::',
];

$defaultOptions = [
    'model_id' => '[MODEL ID]',
    'text' => 'Hello, world!',
    'target_language' => 'fr',
    'source_language' => 'en',
    'project_id' => '[Google Cloud Project ID]',
    'location' => 'global',
];

$options = getopt('', $opts);
$options += $defaultOptions;

$modelId = $options['model_id'];
$text = $options['text'];
$targetLanguage = $options['target_language'];
$sourceLanguage = $options['source_language'];
$projectId = $options['project_id'];
$location = $options['location'];

sampleTranslateTextWithModel($modelId, $text, $targetLanguage, $sourceLanguage, $projectId, $location);
