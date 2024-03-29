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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/language/README.md
 */

namespace Google\Cloud\Samples\Language;

# [START analyze_all_from_file]
use Google\Cloud\Language\V1\AnnotateTextRequest;
use Google\Cloud\Language\V1\AnnotateTextRequest\Features;
use Google\Cloud\Language\V1\Client\LanguageServiceClient;
use Google\Cloud\Language\V1\Document;
use Google\Cloud\Language\V1\Document\Type;
use Google\Cloud\Language\V1\Entity\Type as EntityType;
use Google\Cloud\Language\V1\EntityMention\Type as MentionType;
use Google\Cloud\Language\V1\PartOfSpeech\Tag;

/**
 * @param string $uri The cloud storage object to analyze (gs://your-bucket-name/your-object-name)
 */
function analyze_all_from_file(string $uri): void
{
    // Create the Natural Language client
    $languageServiceClient = new LanguageServiceClient();

    // Create a new Document, pass GCS URI and set type to PLAIN_TEXT
    $document = (new Document())
        ->setGcsContentUri($uri)
        ->setType(Type::PLAIN_TEXT);

    // Set Features to extract ['entities', 'syntax', 'sentiment']
    $features = (new Features())
        ->setExtractEntities(true)
        ->setExtractSyntax(true)
        ->setExtractDocumentSentiment(true);

    // Collect annotations
    $request = (new AnnotateTextRequest())
        ->setDocument($document)
        ->setFeatures($features);
    $response = $languageServiceClient->annotateText($request);

    // Process Entities
    $entities = $response->getEntities();
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
        printf('Mentions:' . PHP_EOL);
        foreach ($entity->getMentions() as $mention) {
            printf('  Begin Offset: %s' . PHP_EOL, $mention->getText()->getBeginOffset());
            printf('  Content: %s' . PHP_EOL, $mention->getText()->getContent());
            printf('  Mention Type: %s' . PHP_EOL, MentionType::name($mention->getType()));
            printf(PHP_EOL);
        }
        printf(PHP_EOL);
    }

    // Process Sentiment
    $document_sentiment = $response->getDocumentSentiment();

    // Print document information
    printf('Document Sentiment:' . PHP_EOL);
    printf('  Magnitude: %s' . PHP_EOL, $document_sentiment->getMagnitude());
    printf('  Score: %s' . PHP_EOL, $document_sentiment->getScore());
    printf(PHP_EOL);
    $sentences = $response->getSentences();
    foreach ($sentences as $sentence) {
        printf('Sentence: %s' . PHP_EOL, $sentence->getText()->getContent());
        printf('Sentence Sentiment:' . PHP_EOL);
        $sentiment = $sentence->getSentiment();
        if ($sentiment) {
            printf('Entity Magnitude: %s' . PHP_EOL, $sentiment->getMagnitude());
            printf('Entity Score: %s' . PHP_EOL, $sentiment->getScore());
        }
        printf(PHP_EOL);
    }

    // Process Syntax
    $tokens = $response->getTokens();

    // Print out information about each entity
    foreach ($tokens as $token) {
        printf('Token text: %s' . PHP_EOL, $token->getText()->getContent());
        printf('Token part of speech: %s' . PHP_EOL, Tag::name($token->getPartOfSpeech()->getTag()));
        printf(PHP_EOL);
    }
}
# [END analyze_all_from_file]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
