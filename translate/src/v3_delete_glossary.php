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
    return printf("Usage: php %s PROJECT_ID GLOSSARY_ID\n", __FILE__);
}
list($_, $projectId, $glossaryId) = $argv;

// [START translate_v3_delete_glossary]
use Google\Cloud\Translate\V3\TranslationServiceClient;

$translationServiceClient = new TranslationServiceClient();

/** Uncomment and populate these variables in your code */
// $projectId = '[Google Cloud Project ID]';
// $glossaryId = '[Glossary ID]';
$formattedName = $translationServiceClient->glossaryName(
    $projectId,
    'us-central1',
    $glossaryId
);

try {
    $operationResponse = $translationServiceClient->deleteGlossary($formattedName);
    $operationResponse->pollUntilComplete();
    if ($operationResponse->operationSucceeded()) {
        $response = $operationResponse->getResult();
        printf('Deleted Glossary.' . PHP_EOL);
    } else {
        $error = $operationResponse->getError();
        // handleError($error)
    }
} finally {
    $translationServiceClient->close();
}
// [END translate_v3_delete_glossary]
