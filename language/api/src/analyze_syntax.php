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

# [START analyze_syntax]
namespace Google\Cloud\Samples\Language;

use Google\Cloud\Language\LanguageClient;

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
    $language = new LanguageClient([
        'projectId' => $projectId,
    ]);

    // Call the analyzeSyntax function
    $annotation = $language->analyzeSyntax($text);

    // Print syntax information. See https://cloud.google.com/natural-language/docs/reference/rest/v1/Token
    // to learn about more information you can extract from Token objects.
    $tokens = $annotation->tokens();
    foreach ($tokens as $token) {
        printf('Token text: %s' . PHP_EOL, $token['text']['content']);
        printf('Token part of speech: %s' . PHP_EOL, $token['partOfSpeech']['tag']);
        printf(PHP_EOL);
    }
}
# [END analyze_syntax]
