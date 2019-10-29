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

if (count($argv) < 4 || count($argv) > 4) {
    return printf("Usage: php %s PROJECT_ID GLOSSARY_ID INPUT_URI\n", __FILE__);
}
list($_, $projectId, $glossaryId, $inputUri) = $argv;

// [START translate_v3_create_glossary]
use Google\Cloud\Translate\V3\GcsSource;
use Google\Cloud\Translate\V3\Glossary;
use Google\Cloud\Translate\V3\GlossaryInputConfig;
use Google\Cloud\Translate\V3\Glossary\LanguageCodesSet;
use Google\Cloud\Translate\V3\TranslationServiceClient;

$translationServiceClient = new TranslationServiceClient();

/** Uncomment and populate these variables in your code */
// $projectId = '[Google Cloud Project ID]';
// $glossaryId = 'my_glossary_id_123';
// $inputUri = 'gs://cloud-samples-data/translation/glossary.csv';
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
    $operationResponse = $translationServiceClient->createGlossary(
        $formattedParent,
        $glossary
    );
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
// [END translate_v3_create_glossary]
