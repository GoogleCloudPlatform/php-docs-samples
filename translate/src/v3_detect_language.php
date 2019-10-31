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
    return printf("Usage: php %s TEXT PROJECT_ID\n", __FILE__);
}
list($_, $text, $projectId) = $argv;

// [START translate_v3_detect_language]
use Google\Cloud\Translate\V3\TranslationServiceClient;

$translationServiceClient = new TranslationServiceClient();

/** Uncomment and populate these variables in your code */
// $text = 'Hello, world!';
// $projectId = '[Google Cloud Project ID]';
$formattedParent = $translationServiceClient->locationName($projectId, 'global');

// Optional. Can be "text/plain" or "text/html".
$mimeType = 'text/plain';

try {
    $response = $translationServiceClient->detectLanguage(
        $formattedParent,
        [
            'content' => $text,
            'mimeType' => $mimeType
        ]
    );
    // Display list of detected languages sorted by detection confidence.
    // The most probable language is first.
    foreach ($response->getLanguages() as $language) {
        // The language detected
        printf('Language code: %s' . PHP_EOL, $language->getLanguageCode());
        // Confidence of detection result for this language
        printf('Confidence: %s' . PHP_EOL, $language->getConfidence());
    }
} finally {
    $translationServiceClient->close();
}
// [END translate_v3_detect_language]
