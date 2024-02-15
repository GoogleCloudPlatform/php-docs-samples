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

namespace Google\Cloud\Samples\Translate;

// [START translate_v3_detect_language]
use Google\Cloud\Translate\V3\Client\TranslationServiceClient;
use Google\Cloud\Translate\V3\DetectLanguageRequest;

/**
 * @param string $text      The text whose language to detect.  This will be detected as en.
 * @param string $projectId Your Google Cloud project ID.
 */
function v3_detect_language(string $text, string $projectId): void
{
    $translationServiceClient = new TranslationServiceClient();

    /** Uncomment and populate these variables in your code */
    // $text = 'Hello, world!';
    // $projectId = '[Google Cloud Project ID]';
    $formattedParent = $translationServiceClient->locationName($projectId, 'global');

    // Optional. Can be "text/plain" or "text/html".
    $mimeType = 'text/plain';

    try {
        $request = (new DetectLanguageRequest())
            ->setParent($formattedParent)
            ->setContent($text)
            ->setMimeType($mimeType);
        $response = $translationServiceClient->detectLanguage($request);
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

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
