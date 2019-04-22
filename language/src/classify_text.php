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

# [START language_classify_text]
namespace Google\Cloud\Samples\Language;

use Google\Cloud\Language\V1\Document;
use Google\Cloud\Language\V1\Document\Type;
use Google\Cloud\Language\V1\LanguageServiceClient;

/**
 * Classify text (20+ words) into categories.
 * ```
 * classify_text(
 *     'The first two gubernatorial elections since President Donald Trump ' .
 *     'took office went in favor of Democratic candidates in Virginia and ' .
 *     'New Jersey.'
 * );
 * ```
 *
 * @param string $text The text to analyze.
 * @param string $projectId (optional) Your Google Cloud Project ID
 */

function classify_text($text, $projectId = null)
{
    // Make sure we have enough words (20+) to call classifyText
    if (str_word_count($text) < 20) {
        printf('20+ words are required to classify text.' . PHP_EOL);
        return;
    }
    $languageServiceClient = new LanguageServiceClient(['projectId' => $projectId]);
    try {
        // Create a new Document
        $document = new Document();
        // Pass GCS URI and set document type to PLAIN_TEXT (1)
        $document->setContent($text)->setType(Type::PLAIN_TEXT);
        // Call the analyzeSentiment function
        $response = $languageServiceClient->classifyText($document);
        $categories = $response->getCategories();
        // Print document information
        foreach ($categories as $category) {
            printf('Category Name: %s' . PHP_EOL, $category->getName());
            printf('Confidence: %s' . PHP_EOL, $category->getConfidence());
            print(PHP_EOL);
        }
    } finally {
        $languageServiceClient->close();
    }
}
# [END language_classify_text]
