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
 * DO NOT EDIT! This is a generated sample ("LongRunningRequest",  "batch_translate_text_with_glossary")
 */

// sample-metadata
//   title: Batch Translate Text with Glossary
//   description: Batch Translate Text with Glossary a given URI using a glossary.
//   usage: php v3_batch_translate_text_with_glossary.php [--input_uri "gs://cloud-samples-data/text.txt"] [--output_uri "gs://YOUR_BUCKET_ID/path_to_store_results/"] [--project "[Google Cloud Project ID]"] [--location "us-central1"] [--glossary_path "projects/[PROJECT_ID]/locations/[LOCATION]/glossaries/[YOUR_GLOSSARY_ID]"] [--target_language en] [--source_language de]
// [START batch_translate_text_with_glossary]
require_once __DIR__ . '/../../vendor/autoload.php';

use Google\Cloud\Translate\V3\TranslationServiceClient;
use Google\Cloud\Translate\V3\GcsDestination;
use Google\Cloud\Translate\V3\GcsSource;
use Google\Cloud\Translate\V3\InputConfig;
use Google\Cloud\Translate\V3\OutputConfig;
use Google\Cloud\Translate\V3\TranslateTextGlossaryConfig;

/**
 * Batch Translate Text with Glossary a given URI using a glossary.
 *
 * @param string $glossaryPath   Required. Specifies the glossary used for this translation.
 * @param string $targetLanguage Required. Specify up to 10 language codes here.
 * @param string $sourceLanguage Required. Source language code.
 */
function sampleBatchTranslateText($inputUri, $outputUri, $project, $location, $glossaryPath, $targetLanguage, $sourceLanguage)
{
    $translationServiceClient = new TranslationServiceClient();

    // $inputUri = 'gs://cloud-samples-data/text.txt';
    // $outputUri = 'gs://YOUR_BUCKET_ID/path_to_store_results/';
    // $project = '[Google Cloud Project ID]';
    // $location = 'us-central1';
    // $glossaryPath = 'projects/[PROJECT_ID]/locations/[LOCATION]/glossaries/[YOUR_GLOSSARY_ID]';
    // $targetLanguage = 'en';
    // $sourceLanguage = 'de';
    $targetLanguageCodes = [$targetLanguage];
    $gcsSource = new GcsSource();
    $gcsSource->setInputUri($inputUri);

    // Optional. Can be "text/plain" or "text/html".
    $mimeType = 'text/plain';
    $inputConfigsElement = new InputConfig();
    $inputConfigsElement->setGcsSource($gcsSource);
    $inputConfigsElement->setMimeType($mimeType);
    $inputConfigs = [$inputConfigsElement];
    $gcsDestination = new GcsDestination();
    $gcsDestination->setOutputUriPrefix($outputUri);
    $outputConfig = new OutputConfig();
    $outputConfig->setGcsDestination($gcsDestination);
    $formattedParent = $translationServiceClient->locationName($project, $location);
    $glossariesItem = new TranslateTextGlossaryConfig();
    $glossariesItem->setGlossary($glossaryPath);
    $glossaries = ['ja' => $glossariesItem];

    try {
        $operationResponse = $translationServiceClient->batchTranslateText($sourceLanguage, $targetLanguageCodes, $inputConfigs, $outputConfig, ['parent' => $formattedParent, 'glossaries' => $glossaries]);
        $operationResponse->pollUntilComplete();
        if ($operationResponse->operationSucceeded()) {
            $response = $operationResponse->getResult();
            // Display the translation for each input text provided
            printf('Total Characters: %s' . PHP_EOL, $response->getTotalCharacters());
            printf('Translated Characters: %s' . PHP_EOL, $response->getTranslatedCharacters());
        } else {
            $error = $operationResponse->getError();
            // handleError($error)
        }
    } finally {
        $translationServiceClient->close();
    }
}
// [END batch_translate_text_with_glossary]

$opts = [
    'input_uri::',
    'output_uri::',
    'project::',
    'location::',
    'glossary_path::',
    'target_language::',
    'source_language::',
];

$defaultOptions = [
    'input_uri' => 'gs://cloud-samples-data/text.txt',
    'output_uri' => 'gs://YOUR_BUCKET_ID/path_to_store_results/',
    'project' => '[Google Cloud Project ID]',
    'location' => 'us-central1',
    'glossary_path' => 'projects/[PROJECT_ID]/locations/[LOCATION]/glossaries/[YOUR_GLOSSARY_ID]',
    'target_language' => 'en',
    'source_language' => 'de',
];

$options = getopt('', $opts);
$options += $defaultOptions;

$inputUri = $options['input_uri'];
$outputUri = $options['output_uri'];
$project = $options['project'];
$location = $options['location'];
$glossaryPath = $options['glossary_path'];
$targetLanguage = $options['target_language'];
$sourceLanguage = $options['source_language'];

sampleBatchTranslateText($inputUri, $outputUri, $project, $location, $glossaryPath, $targetLanguage, $sourceLanguage);
