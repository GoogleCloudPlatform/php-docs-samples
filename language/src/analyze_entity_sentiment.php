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

# [START language_entity_sentiment_text]
namespace Google\Cloud\Samples\Language;

use Google\Cloud\Language\V1beta2\Document;
use Google\Cloud\Language\V1beta2\Document\Type;
use Google\Cloud\Language\V1beta2\LanguageServiceClient;

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
    $languageServiceClient = new LanguageServiceClient(['projectId' => $projectId]);
    try {
        $entity_types = [
            0 => 'UNKNOWN',
            1 => 'PERSON',
            2 => 'LOCATION',
            3 => 'ORGANIZATION',
            4 => 'EVENT',
            5 => 'WORK_OF_ART',
            6 => 'CONSUMER_GOOD',
            7 => 'OTHER',
        ];
        // Create a new Document
        $document = new Document();
        // Add text as content and set document type to PLAIN_TEXT
        $document->setContent($text)->setType(Type::PLAIN_TEXT);
        // Call the analyzeEntitySentiment function
        $response = $languageServiceClient->analyzeEntitySentiment($document);
        $entities = $response->getEntities();
        // Print out information about each entity
        foreach ($entities as $entity) {
            printf('Entity Name: %s' . PHP_EOL, $entity->getName());
            printf('Entity Type: %s' . PHP_EOL, $entity_types[$entity->getType()]);
            printf('Entity Salience: %s' . PHP_EOL, $entity->getSalience());
            $sentiment = $entity->getSentiment();
            if ($sentiment) {
                printf('Entity Magnitude: %s' . PHP_EOL, $sentiment->getMagnitude());
                printf('Entity Score: %s' . PHP_EOL, $sentiment->getScore());
            }
            print(PHP_EOL);
        }
    } finally {
        $languageServiceClient->close();
    }
}
# [END language_entity_sentiment_text]
