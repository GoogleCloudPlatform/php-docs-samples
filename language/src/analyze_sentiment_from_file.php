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

# [START language_sentiment_gcs]
namespace Google\Cloud\Samples\Language;

use Google\Cloud\Language\V1beta2\Document;
use Google\Cloud\Language\V1beta2\Document\Type;
use Google\Cloud\Language\V1beta2\LanguageServiceClient;

/**
 * Find the sentiment in text stored in a Cloud Storage bucket.
 * ```
 * analyze_sentiment_from_file('my-bucket', 'file_with_text.txt');
 * ```
 *
 * @param string $gcsUri Your Cloud Storage bucket URI
 * @param string $projectId (optional) Your Google Cloud Project ID
 *
 */
function analyze_sentiment_from_file($gcsUri, $projectId = null)
{
    $languageServiceClient = new LanguageServiceClient(['projectId' => $projectId]);
    try {
        // Create a new Document
        $document = new Document();
        // Pass GCS URI and set document type to PLAIN_TEXT
        $document->setGcsContentUri($gcsUri)->setType(Type::PLAIN_TEXT);
        // Call the analyzeSentiment function
        $response = $languageServiceClient->analyzeSentiment($document);
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
            print(PHP_EOL);
        }
    } finally {
        $languageServiceClient->close();
    }
}
# [END language_sentiment_gcs]
