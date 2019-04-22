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
    return printf("Usage: php %s FILE\n", __FILE__);
}
list($_, $uri) = $argv;

# [START language_classify_gcs]
use Google\Cloud\Language\V1\Document;
use Google\Cloud\Language\V1\Document\Type;
use Google\Cloud\Language\V1\LanguageServiceClient;

/** Uncomment and populate these variables in your code */
// $uri = 'The cloud storage object to analyze (gs://your-bucket-name/your-object-name)';

$languageServiceClient = new LanguageServiceClient();
try {
    // Create a new Document, pass GCS URI and set type to PLAIN_TEXT
    $document = (new Document())
        ->setGcsContentUri($uri)
        ->setType(Type::PLAIN_TEXT);

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
# [END language_classify_gcs]
