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
 * DO NOT EDIT! This is a generated sample ("LongRunningRequest",  "translate_v3_create_glossary")
 */

// sample-metadata
//   title: Create Glossary
//   description: Create Glossary
//   usage: php v3_create_glossary.php [--project_id "[Google Cloud Project ID]"] [--project_2 "[Your Google Cloud Project ID]"] [--glossary_id "my_glossary_id_123"] [--input_uri "gs://translation_samples_beta/glossaries/glossary.csv"]
// [START translate_v3_create_glossary]
require_once __DIR__ . '/../../vendor/autoload.php';

use Google\Cloud\Translate\V3\TranslationServiceClient;
use Google\Cloud\Translate\V3\GcsSource;
use Google\Cloud\Translate\V3\Glossary;
use Google\Cloud\Translate\V3\GlossaryInputConfig;
use Google\Cloud\Translate\V3\Glossary\LanguageCodesSet;

/** Create Glossary */
function sampleCreateGlossary($projectId, $project2, $glossaryId, $inputUri)
{
    $translationServiceClient = new TranslationServiceClient();

    // $projectId = '[Google Cloud Project ID]';
    // $project2 = '[Your Google Cloud Project ID]';
    // $glossaryId = 'my_glossary_id_123';
    // $inputUri = 'gs://translation_samples_beta/glossaries/glossary.csv';
    $formattedParent = $translationServiceClient->locationName($projectId, 'us-central1');
    $formattedName = $translationServiceClient->glossaryName($project2, 'us-central1', $glossaryId);
    $languageCodesElement = 'en';
    $languageCodesElement2 = 'ja';
    $languageCodes = [$languageCodesElement, $languageCodesElement2];
    $languageCodesSet = new LanguageCodesSet();
    $languageCodesSet->setLanguageCodes($languageCodes);
    $gcsSource = new GcsSource();
    $gcsSource->setInputUri($inputUri);
    $inputConfig = new GlossaryInputConfig();
    $inputConfig->setGcsSource($gcsSource);
    $glossary = new Glossary();
    $glossary->setName($formattedName);
    $glossary->setLanguageCodesSet($languageCodesSet);
    $glossary->setInputConfig($inputConfig);

    try {
        $operationResponse = $translationServiceClient->createGlossary($formattedParent, $glossary);
        $operationResponse->pollUntilComplete();
        if ($operationResponse->operationSucceeded()) {
            $response = $operationResponse->getResult();
            printf('Created Glossary.' . PHP_EOL);
            printf('Glossary name: %s' . PHP_EOL, $response->getName());
            printf('Entry count: %s' . PHP_EOL, $response->getEntryCount());
            printf('Input URI: %s' . PHP_EOL, $response->getInputConfig()->getGcsSource()->getInputUri());
        } else {
            $error = $operationResponse->getError();
            // handleError($error)
        }
    } finally {
        $translationServiceClient->close();
    }
}
// [END translate_v3_create_glossary]

$opts = [
    'project_id::',
    'project_2::',
    'glossary_id::',
    'input_uri::',
];

$defaultOptions = [
    'project_id' => '[Google Cloud Project ID]',
    'project_2' => '[Your Google Cloud Project ID]',
    'glossary_id' => 'my_glossary_id_123',
    'input_uri' => 'gs://translation_samples_beta/glossaries/glossary.csv',
];

$options = getopt('', $opts);
$options += $defaultOptions;

$projectId = $options['project_id'];
$project2 = $options['project_2'];
$glossaryId = $options['glossary_id'];
$inputUri = $options['input_uri'];

sampleCreateGlossary($projectId, $project2, $glossaryId, $inputUri);
