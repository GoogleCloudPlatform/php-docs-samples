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
 * DO NOT EDIT! This is a generated sample ("Request",  "translate_v3_get_supported_languages")
 */

// sample-metadata
//   title: List Supported Language Codes
//   description: Getting a list of supported language codes
//   usage: php v3_get_supported_languages.php [--project_id "[Google Cloud Project ID]"]
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) < 2 || count($argv) > 2) {
    return printf("Usage: php %s PROJECT_ID\n", __FILE__);
}
list($_, $projectId) = $argv;

// [START translate_v3_get_supported_languages]
use Google\Cloud\Translate\V3\TranslationServiceClient;

/** Getting a list of supported language codes */
$translationServiceClient = new TranslationServiceClient();

// $projectId = '[Google Cloud Project ID]';
$formattedParent = $translationServiceClient->locationName($projectId, 'global');

try {
    $response = $translationServiceClient->getSupportedLanguages($formattedParent);
    // List language codes of supported languages
    foreach ($response->getLanguages() as $language) {
        printf('Language Code: %s' . PHP_EOL, $language->getLanguageCode());
    }
} finally {
    $translationServiceClient->close();
}
// [END translate_v3_get_supported_languages]
