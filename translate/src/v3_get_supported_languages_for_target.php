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

if (count($argv) < 3 || count($argv) > 3) {
    return printf("Usage: php %s LANGUAGE_CODE PROJECT_ID\n", __FILE__);
}
list($_, $languageCode, $projectId) = $argv;

// [START translate_v3_get_supported_languages_for_target]
use Google\Cloud\Translate\V3\TranslationServiceClient;

$translationServiceClient = new TranslationServiceClient();

/** Uncomment and populate these variables in your code */
// $languageCode = 'en';
// $projectId = '[Google Cloud Project ID]';
$formattedParent = $translationServiceClient->locationName($projectId, 'global');

try {
    $response = $translationServiceClient->getSupportedLanguages(
        $formattedParent,
        ['displayLanguageCode' => $languageCode]
    );
    // List language codes of supported languages
    foreach ($response->getLanguages() as $language) {
        printf('Language Code: %s' . PHP_EOL, $language->getLanguageCode());
        printf('Display Name: %s' . PHP_EOL, $language->getDisplayName());
    }
} finally {
    $translationServiceClient->close();
}
// [END translate_v3_get_supported_languages_for_target]
