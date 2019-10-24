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
 * DO NOT EDIT! This is a generated sample ("Request",  "translate_v3_detect_language")
 */

// sample-metadata
//   title: Detect Language
//   description: Detecting the language of a text string
//   usage: php v3_detect_language.php [--text "Hello, world!"] [--project_id "[Google Cloud Project ID]"]
// [START translate_v3_detect_language]
require_once __DIR__ . '/../vendor/autoload.php';

use Google\Cloud\Translate\V3\TranslationServiceClient;

/**
 * Detecting the language of a text string.
 *
 * @param string $text The text string for performing language detection
 */
function sampleDetectLanguage($text, $projectId)
{
    $translationServiceClient = new TranslationServiceClient();

    // $text = 'Hello, world!';
    // $projectId = '[Google Cloud Project ID]';
    $formattedParent = $translationServiceClient->locationName($projectId, 'global');

    // Optional. Can be "text/plain" or "text/html".
    $mimeType = 'text/plain';

    try {
        $response = $translationServiceClient->detectLanguage($formattedParent, ['content' => $text, 'mimeType' => $mimeType]);
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
}
// [END translate_v3_detect_language]

$opts = [
    'text::',
    'project_id::',
];

$defaultOptions = [
    'text' => 'Hello, world!',
    'project_id' => '[Google Cloud Project ID]',
];

$options = getopt('', $opts);
$options += $defaultOptions;

$text = $options['text'];
$projectId = $options['project_id'];

sampleDetectLanguage($text, $projectId);
