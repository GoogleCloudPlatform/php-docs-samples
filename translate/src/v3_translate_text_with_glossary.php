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

require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) < 6 || count($argv) > 6) {
    return printf("Usage: php %s TEXT SOURCE_LANGUAGE TARGET_LANGUAGE PROJECT_ID GLOSSARY_ID\n", __FILE__);
}
list($_, $text, $sourceLanguage, $targetLanguage, $projectId, $glossaryId) = $argv;

// [START translate_v3_translate_text_with_glossary]
use Google\Cloud\Translate\V3\TranslateTextGlossaryConfig;
use Google\Cloud\Translate\V3\TranslationServiceClient;

$translationServiceClient = new TranslationServiceClient();

/** Uncomment and populate these variables in your code */
// $text = 'Hello, world!';
// $sourceLanguage = 'en';
// $targetLanguage = 'fr';
// $projectId = '[Google Cloud Project ID]';
// $glossaryId = '[YOUR_GLOSSARY_ID]';
$glossaryPath = $translationServiceClient->glossaryName(
    $projectId,
    'us-central1',
    $glossaryId
);
$contents = [$text];
$formattedParent = $translationServiceClient->locationName(
    $projectId,
    'us-central1'
);
$glossaryConfig = new TranslateTextGlossaryConfig();
$glossaryConfig->setGlossary($glossaryPath);

// Optional. Can be "text/plain" or "text/html".
$mimeType = 'text/plain';

try {
    $response = $translationServiceClient->translateText(
        $contents,
        $targetLanguage,
        $formattedParent,
        [
            'sourceLanguageCode' => $sourceLanguage,
            'glossaryConfig' => $glossaryConfig,
            'mimeType' => $mimeType
        ]
    );
    // Display the translation for each input text provided
    foreach ($response->getGlossaryTranslations() as $translation) {
        printf('Translated text: %s' . PHP_EOL, $translation->getTranslatedText());
    }
} finally {
    $translationServiceClient->close();
}
// [END translate_v3_translate_text_with_glossary]
