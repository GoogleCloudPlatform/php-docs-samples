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

// [START translate_v3_get_supported_languages_for_target]
use Google\Cloud\Translate\V3\Client\TranslationServiceClient;
use Google\Cloud\Translate\V3\GetSupportedLanguagesRequest;

/**
 * @param string $projectId     Your Google Cloud project ID.
 * @param string $languageCode  Languages to list that are supported by this language code.
 */
function v3_get_supported_languages_for_target(string $languageCode, string $projectId): void
{
    $translationServiceClient = new TranslationServiceClient();

    $formattedParent = $translationServiceClient->locationName($projectId, 'global');

    try {
        $request = (new GetSupportedLanguagesRequest())
            ->setParent($formattedParent)
            ->setDisplayLanguageCode($languageCode);
        $response = $translationServiceClient->getSupportedLanguages($request);
        // List language codes of supported languages
        foreach ($response->getLanguages() as $language) {
            printf('Language Code: %s' . PHP_EOL, $language->getLanguageCode());
            printf('Display Name: %s' . PHP_EOL, $language->getDisplayName());
        }
    } finally {
        $translationServiceClient->close();
    }
}
// [END translate_v3_get_supported_languages_for_target]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
