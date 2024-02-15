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

// [START translate_v3_get_glossary]
use Google\Cloud\Translate\V3\Client\TranslationServiceClient;
use Google\Cloud\Translate\V3\GetGlossaryRequest;

/**
 * @param string $projectId     Your Google Cloud project ID.
 * @param string $glossaryId    Your glossary ID.
 */
function v3_get_glossary(string $projectId, string $glossaryId): void
{
    $translationServiceClient = new TranslationServiceClient();

    $formattedName = $translationServiceClient->glossaryName(
        $projectId,
        'us-central1',
        $glossaryId
    );

    try {
        $request = (new GetGlossaryRequest())
            ->setName($formattedName);
        $response = $translationServiceClient->getGlossary($request);
        printf('Glossary name: %s' . PHP_EOL, $response->getName());
        printf('Entry count: %s' . PHP_EOL, $response->getEntryCount());
        printf(
            'Input URI: %s' . PHP_EOL,
            $response->getInputConfig()
                ->getGcsSource()
                ->getInputUri()
        );
    } finally {
        $translationServiceClient->close();
    }
}
// [END translate_v3_get_glossary]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
