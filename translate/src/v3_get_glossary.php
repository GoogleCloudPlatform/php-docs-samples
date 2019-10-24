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
 * DO NOT EDIT! This is a generated sample ("Request",  "translate_v3_get_glossary")
 */

// sample-metadata
//   title: Get Glossary
//   description: Get Glossary
//   usage: php v3_get_glossary.php [--project "[Google Cloud Project ID]"] [--glossary_id "[Glossary ID]"]
// [START translate_v3_get_glossary]
require_once __DIR__ . '/../vendor/autoload.php';

use Google\Cloud\Translate\V3\TranslationServiceClient;

/** Get Glossary */
function sampleGetGlossary($project, $glossaryId)
{
    $translationServiceClient = new TranslationServiceClient();

    // $project = '[Google Cloud Project ID]';
    // $glossaryId = '[Glossary ID]';
    $formattedName = $translationServiceClient->glossaryName($project, 'us-central1', $glossaryId);

    try {
        $response = $translationServiceClient->getGlossary($formattedName);
        printf('Glossary name: %s' . PHP_EOL, $response->getName());
        printf('Entry count: %s' . PHP_EOL, $response->getEntryCount());
        printf('Input URI: %s' . PHP_EOL, $response->getInputConfig()->getGcsSource()->getInputUri());
    } finally {
        $translationServiceClient->close();
    }
}
// [END translate_v3_get_glossary]

$opts = [
    'project::',
    'glossary_id::',
];

$defaultOptions = [
    'project' => '[Google Cloud Project ID]',
    'glossary_id' => '[Glossary ID]',
];

$options = getopt('', $opts);
$options += $defaultOptions;

$project = $options['project'];
$glossaryId = $options['glossary_id'];

sampleGetGlossary($project, $glossaryId);
