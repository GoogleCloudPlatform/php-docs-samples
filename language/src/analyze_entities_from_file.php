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

# [START language_entities_gcs]
namespace Google\Cloud\Samples\Language;

use Google\Cloud\Language\V1beta2\Document;
use Google\Cloud\Language\V1beta2\LanguageServiceClient;

/**
 * Find the entities in text stored in a Cloud Storage bucket.
 * ```
 * analyze_entities_from_file('gs://my-bucket/text.txt');
 * ```
 *
 * @param string $gcsUri The Cloud Storage path with text.
 * @param string $projectId (optional) Your Google Cloud Project ID
 *
 */
function analyze_entities_from_file($gcsUri, $projectId = null)
{
    // Create the Natural Language client
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
        $document = new Document();
        // Pass GCS URI and set document type to PLAIN_TEXT
        $document->setGcsContentUri($gcsUri)->setType(1);
        // Call the analyzeEntities function
        $response = $languageServiceClient->analyzeEntities($document, []);
        $entities = $response->getEntities();
        // Print out information about each entity
        foreach ($entities as $entity) {
            printf('Name: %s' . PHP_EOL, $entity->getName());
            printf('Type: %s' . PHP_EOL, $entity_types[$entity->getType()]);
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
}
# [END language_entities_gcs]
