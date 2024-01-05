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

// [START translate_v3_create_glossary]
use Google\Cloud\Translate\V3\Client\TranslationServiceClient;
use Google\Cloud\Translate\V3\CreateGlossaryRequest;
use Google\Cloud\Translate\V3\GcsSource;
use Google\Cloud\Translate\V3\Glossary;
use Google\Cloud\Translate\V3\Glossary\LanguageCodesSet;
use Google\Cloud\Translate\V3\GlossaryInputConfig;

/**
 * @param string $projectId     Your Google Cloud project ID.
 * @param string $glossaryId    Your glossary ID.
 * @param string $inputUri      Path to to source input (e.g. "gs://cloud-samples-data/translation/glossary.csv").
 */
function v3_create_glossary(
    string $projectId,
    string $glossaryId,
    string $inputUri
): void {
    $translationServiceClient = new TranslationServiceClient();

    $formattedParent = $translationServiceClient->locationName(
        $projectId,
        'us-central1'
    );
    $formattedName = $translationServiceClient->glossaryName(
        $projectId,
        'us-central1',
        $glossaryId
    );
    $languageCodesElement = 'en';
    $languageCodesElement2 = 'ja';
    $languageCodes = [$languageCodesElement, $languageCodesElement2];
    $languageCodesSet = new LanguageCodesSet();
    $languageCodesSet->setLanguageCodes($languageCodes);
    $gcsSource = (new GcsSource())
        ->setInputUri($inputUri);
    $inputConfig = (new GlossaryInputConfig())
        ->setGcsSource($gcsSource);
    $glossary = (new Glossary())
        ->setName($formattedName)
        ->setLanguageCodesSet($languageCodesSet)
        ->setInputConfig($inputConfig);

    try {
        $request = (new CreateGlossaryRequest())
            ->setParent($formattedParent)
            ->setGlossary($glossary);
        $operationResponse = $translationServiceClient->createGlossary($request);
        $operationResponse->pollUntilComplete();
        if ($operationResponse->operationSucceeded()) {
            $response = $operationResponse->getResult();
            printf('Created Glossary.' . PHP_EOL);
            printf('Glossary name: %s' . PHP_EOL, $response->getName());
            printf('Entry count: %s' . PHP_EOL, $response->getEntryCount());
            printf(
                'Input URI: %s' . PHP_EOL,
                $response->getInputConfig()
                    ->getGcsSource()
                    ->getInputUri()
            );
        } else {
            $error = $operationResponse->getError();
            // handleError($error)
        }
    } finally {
        $translationServiceClient->close();
    }
}
// [END translate_v3_create_glossary]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
