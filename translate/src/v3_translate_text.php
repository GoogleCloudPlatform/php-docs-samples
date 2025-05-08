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

// [START translate_v3_translate_text]
// [START translate_v3_import_client_library]
use Google\Cloud\Translate\V3\Client\TranslationServiceClient;
// [END translate_v3_import_client_library]
use Google\Cloud\Translate\V3\TranslateTextRequest;

/**
 * @param string $text           The text to translate.
 * @param string $targetLanguage Language to translate to.
 * @param string $projectId      Your Google Cloud project ID.
 */
function v3_translate_text(
    string $text,
    string $targetLanguage,
    string $projectId
): void {
    $translationServiceClient = new TranslationServiceClient();

    $contents = [$text];
    $formattedParent = $translationServiceClient->locationName($projectId, 'global');

    try {
        $request = (new TranslateTextRequest())
            ->setContents($contents)
            ->setTargetLanguageCode($targetLanguage)
            ->setParent($formattedParent);
        $response = $translationServiceClient->translateText($request);

        // Display the translation for each input text provided
        foreach ($response->getTranslations() as $translation) {
            printf('Translated text: %s' . PHP_EOL, $translation->getTranslatedText());
        }
    } finally {
        $translationServiceClient->close();
    }
}
// [END translate_v3_translate_text]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
