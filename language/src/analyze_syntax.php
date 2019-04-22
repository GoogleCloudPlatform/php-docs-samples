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

# [START language_syntax_text]
namespace Google\Cloud\Samples\Language;

use Google\Cloud\Language\V1\Document;
use Google\Cloud\Language\V1\Document\Type;
use Google\Cloud\Language\V1\LanguageServiceClient;
use Google\Cloud\Language\V1\PartOfSpeech\Tag;

/**
 * Find the syntax in text.
 * ```
 * analyze_syntax('Do you know the way to San Jose?');
 * ```
 *
 * @param string $text The text to analyze.
 * @param string $projectId (optional) Your Google Cloud Project ID
 *
 */
function analyze_syntax($text, $projectId = null)
{
    // Create the Natural Language client
    $languageServiceClient = new LanguageServiceClient(['projectId' => $projectId]);

    try {
        // Create a new Document
        $document = new Document();
        // Add text as content and set document type to PLAIN_TEXT
        $document->setContent($text)->setType(Type::PLAIN_TEXT);
        // Call the analyzeEntities function
        $response = $languageServiceClient->analyzeSyntax($document, []);
        $tokens = $response->getTokens();
        // Print out information about each entity
        foreach ($tokens as $token) {
            printf('Token text: %s' . PHP_EOL, $token->getText()->getContent());
            printf('Token part of speech: %s' . PHP_EOL, Tag::name($token->getPartOfSpeech()->getTag()));
            print(PHP_EOL);
        }
    } finally {
        $languageServiceClient->close();
    }
}
# [END language_syntax_text]
