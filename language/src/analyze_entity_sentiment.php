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

# [START analyze_entity_sentiment]
namespace Google\Cloud\Samples\Language;

use Google\Cloud\Language\V1beta2\LanguageServiceClient;
use Google\Cloud\Language\V1beta2\Document;
use Google\Cloud\Language\V1beta2\Document_Type;
use Google\Cloud\Language\V1beta2\EncodingType;

/**
 * Find the entities in text.
 * ```
 * analyze_entity_sentiment('Do you know the way to San Jose?');
 * ```
 *
 * @param string $text The text to analyze.
 * @param string $projectId (optional) Your Google Cloud Project ID
 *
 */

function analyze_entity_sentiment($text, $projectId = null)
{
    // Create the Natural Language client
    $language = new LanguageServiceClient([
        'projectId' => $projectId,
    ]);
    $document = new Document();
    $document->setType(Document_Type::PLAIN_TEXT);
    $document->setContent($text);
    $encodingType = EncodingType::UTF16;

    // Call the analyzeEntitySentiment function
    $response = $language->analyzeEntitySentiment($document, ['encodingType' => $encodingType]);
    $entities = $response->getEntities();

    $entity_types = array('UNKNOWN', 'PERSON', 'LOCATION', 'ORGANIZATION', 'EVENT',
        'WORK_OF_ART', 'CONSUMER_GOOD', 'OTHER');

    // Print out information about each entity
    foreach ($entities as $entity) {
        printf('Entity Name: %s' . PHP_EOL, $entity->getName());
        printf('Entity Type: %s' . PHP_EOL, $entity_types[$entity->getType()]);
        printf('Entity Salience: %s' . PHP_EOL, $entity->getSalience());
        printf('Entity Magnitude: %s' . PHP_EOL, $entity->getSentiment()->getMagnitude());
        printf('Entity Score: %s' . PHP_EOL, $entity->getSentiment()->getScore());
        printf(PHP_EOL);
    }
}
# [END analyze_entity_sentiment]
