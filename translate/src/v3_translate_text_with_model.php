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

if (count($argv) < 7 || count($argv) > 7) {
    return printf("Usage: php %s MODEL_ID TEXT TARGET_LANGUAGE SOURCE_LANGUAGE PROJECT_ID LOCATION\n", __FILE__);
}
list($_, $modelId, $text, $targetLanguage, $sourceLanguage, $projectId, $location) = $argv;

// [START translate_v3_translate_text_with_model]
use Google\Cloud\Translate\V3\TranslationServiceClient;

$translationServiceClient = new TranslationServiceClient();

/** Uncomment and populate these variables in your code */
// $modelId = '[MODEL ID]';
// $text = 'Hello, world!';
// $targetLanguage = 'fr';
// $sourceLanguage = 'en';
// $projectId = '[Google Cloud Project ID]';
// $location = 'global';
$modelPath = sprintf(
    'projects/%s/locations/%s/models/%s',
    $projectId,
    $location,
    $modelId
);
$contents = [$text];
$formattedParent = $translationServiceClient->locationName(
    $projectId,
    $location
);

// Optional. Can be "text/plain" or "text/html".
$mimeType = 'text/plain';

try {
    $response = $translationServiceClient->translateText(
        $contents,
        $targetLanguage,
        $formattedParent,
        [
            'model' => $modelPath,
            'sourceLanguageCode' => $sourceLanguage,
            'mimeType' => $mimeType
        ]
    );
    // Display the translation for each input text provided
    foreach ($response->getTranslations() as $translation) {
        printf('Translated text: %s' . PHP_EOL, $translation->getTranslatedText());
    }
} finally {
    $translationServiceClient->close();
}
// [END translate_v3_translate_text_with_model]
