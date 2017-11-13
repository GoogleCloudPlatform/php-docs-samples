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

# [START analyze_all]
namespace Google\Cloud\Samples\Language;

use Google\Cloud\Language\LanguageClient;

/**
 * Find the everything in text.
 * ```
 * analyze_all('Do you know the way to San Jose?');
 * ```
 *
 * @param string $text The text to analyze.
 * @param string $projectId (optional) Your Google Cloud Project ID
 *
 */
function analyze_all($text, $projectId = null)
{
    // Create the Natural Language client
    $language = new LanguageClient([
        'projectId' => $projectId,
    ]);

    // Call the annotateText function
    $annotation = $language->annotateText($text, [
        'features' => ['entities', 'syntax', 'sentiment']
    ]);

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
        printf('Mentions:' . PHP_EOL);
        foreach ($entity['mentions'] as $mention) {
            printf('  Begin Offset: %s' . PHP_EOL, $mention['text']['beginOffset']);
            printf('  Content: %s' . PHP_EOL, $mention['text']['content']);
            printf('  Mention Type: %s' . PHP_EOL, $mention['type']);
            printf(PHP_EOL);
        }
        printf(PHP_EOL);
    }

    // Print document and sentence sentiment information
    $sentiment = $annotation->sentiment();
    printf('Document Sentiment:' . PHP_EOL);
    printf('  Magnitude: %s' . PHP_EOL, $sentiment['magnitude']);
    printf('  Score: %s' . PHP_EOL, $sentiment['score']);
    printf(PHP_EOL);
    foreach ($annotation->sentences() as $sentence) {
        printf('Sentence: %s' . PHP_EOL, $sentence['text']['content']);
        printf('Sentence Sentiment:' . PHP_EOL);
        printf('  Magnitude: %s' . PHP_EOL, $sentence['sentiment']['magnitude']);
        printf('  Score: %s' . PHP_EOL, $sentence['sentiment']['score']);
        printf(PHP_EOL);
    }

    // Print syntax information. See https://cloud.google.com/natural-language/docs/reference/rest/v1/Token
    // to learn about more information you can extract from Token objects.
    $tokens = $annotation->tokens();
    foreach ($tokens as $token) {
        printf('Token text: %s' . PHP_EOL, $token['text']['content']);
        printf('Token part of speech: %s' . PHP_EOL, $token['partOfSpeech']['tag']);
        printf(PHP_EOL);
    }
}
# [END analyze_all]
