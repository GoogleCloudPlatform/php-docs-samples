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

// sample-metadata
//   title: Batch Translate Text with Glossary
//   description: Batch Translate Text with Glossary a given URI using a glossary.
//   usage: php v3_batch_translate_text_with_glossary.php [--input_uri "gs://cloud-samples-data/text.txt"] [--output_uri "gs://YOUR_BUCKET_ID/path_to_store_results/"] [--project "[Google Cloud Project ID]"] [--location "us-central1"] [--glossary_id "[YOUR_GLOSSARY_ID]"] [--target_language en] [--source_language de]
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) < 8 || count($argv) > 8) {
    return printf("Usage: php %s INPUT_URI OUTPUT_URI PROJECT_ID LOCATION GLOSSARY_ID TARGET_LANGUAGE SOURCE_LANGUAGE\n", __FILE__);
}
list($_, $inputUri, $outputUri, $projectId, $location, $glossaryId, $targetLanguage, $sourceLanguage) = $argv;

// [START batch_translate_text_with_glossary]
use Google\Cloud\Translate\V3\TranslationServiceClient;
use Google\Cloud\Translate\V3\GcsDestination;
use Google\Cloud\Translate\V3\GcsSource;
use Google\Cloud\Translate\V3\InputConfig;
use Google\Cloud\Translate\V3\OutputConfig;
use Google\Cloud\Translate\V3\TranslateTextGlossaryConfig;

/**
 * Batch Translate Text with Glossary a given URI using a glossary.
 *
 * @param string $glossaryId   Required. Specifies the glossary used for this translation.
 * @param string $targetLanguage Required. Specify up to 10 language codes here.
 * @param string $sourceLanguage Required. Source language code.
 */
$translationServiceClient = new TranslationServiceClient();

// $inputUri = 'gs://cloud-samples-data/text.txt';
// $outputUri = 'gs://YOUR_BUCKET_ID/path_to_store_results/';
// $projectId = '[Google Cloud Project ID]';
// $location = 'us-central1';
// $glossaryId = '[YOUR_GLOSSARY_ID]';
// $targetLanguage = 'en';
// $sourceLanguage = 'de';
$glossaryPath = $translationServiceClient->glossaryName($projectId, $location, $glossaryId);
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
$formattedParent = $translationServiceClient->locationName($projectId, $location);
$glossariesItem = new TranslateTextGlossaryConfig();
$glossariesItem->setGlossary($glossaryPath);
$glossaries = ['ja' => $glossariesItem];

try {
    $operationResponse = $translationServiceClient->batchTranslateText($formattedParent, $sourceLanguage, $targetLanguageCodes, $inputConfigs, $outputConfig, ['glossaries' => $glossaries]);
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
// [END batch_translate_text_with_glossary]
