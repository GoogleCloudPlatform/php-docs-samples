<?php
/**
 * Copyright 2017 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/language/README.md
 */

# [START analyze_entities]
namespace Google\Cloud\Samples\Language;

use Google\Cloud\Language\LanguageClient;

/**
 * Find the entities in text.
 * ```
 * analyze_entities('Do you know the way to San Jose?');
 * ```
 *
 * @param string $text The text to analyze.
 * @param string $projectId (optional) Your Google Cloud Project ID
 *
 */
function analyze_entities($text, $projectId = null)
{
    // Create the Natural Language client
    $language = new LanguageClient([
        'projectId' => $projectId,
    ]);

    // Call the analyzeEntities function
    $annotation = $language->analyzeEntities($text);

    // Print out information about each entity
    $entities = $annotation->entities();
    foreach ($entities as $entity) {
        printf('Name: %s' . PHP_EOL, $entity['name']);
        printf('Type: %s' . PHP_EOL, $entity['type']);
        printf('Salience: %s' . PHP_EOL, $entity['salience']);
        if (array_key_exists('wikipedia_url', $entity['metadata'])) {
            printf('Wikipedia URL: %s' . PHP_EOL, $entity['metadata']['wikipedia_url']);
        }
        if (array_key_exists('mid', $entity['metadata'])) {
            printf('Knowledge Graph MID: %s' . PHP_EOL, $entity['metadata']['mid']);
        }
        printf(PHP_EOL);
    }
}
# [END analyze_entities]
