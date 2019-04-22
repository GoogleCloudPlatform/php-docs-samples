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

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) != 2) {
    return printf("Usage: php %s TEXT\n", __FILE__);
}
list($_, $text) = $argv;

# [START language_entities_text]
use Google\Cloud\Language\V1\Document;
use Google\Cloud\Language\V1\Document\Type;
use Google\Cloud\Language\V1\LanguageServiceClient;
use Google\Cloud\Language\V1\Entity\Type as EntityType;

/** Uncomment and populate these variables in your code */
// $text = 'The text to analyze.';

// Create the Natural Language client
$languageServiceClient = new LanguageServiceClient();
try {
    // Create a new Document, add text as content and set type to PLAIN_TEXT
    $document = (new Document())
        ->setContent($text)
        ->setType(Type::PLAIN_TEXT);

    // Call the analyzeEntities function
    $response = $languageServiceClient->analyzeEntities($document, []);
    $entities = $response->getEntities();
    // Print out information about each entity
    foreach ($entities as $entity) {
        printf('Name: %s' . PHP_EOL, $entity->getName());
        printf('Type: %s' . PHP_EOL, EntityType::name($entity->getType()));
        printf('Salience: %s' . PHP_EOL, $entity->getSalience());
        if ($entity->getMetadata()->offsetExists('wikipedia_url')) {
            printf('Wikipedia URL: %s' . PHP_EOL, $entity->getMetadata()->offsetGet('wikipedia_url'));
        }
        if ($entity->getMetadata()->offsetExists('mid')) {
            printf('Knowledge Graph MID: %s' . PHP_EOL, $entity->getMetadata()->offsetGet('mid'));
        }
        printf(PHP_EOL);
    }
} finally {
    $languageServiceClient->close();
}
# [END language_entities_text]
