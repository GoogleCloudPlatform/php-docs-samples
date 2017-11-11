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

# [START language_classify_file]
namespace Google\Cloud\Samples\Language;

use Google\Cloud\Language\LanguageClient;

/**
 * Classify text (20+ words) into categories.
 * ```
 * classify_text_from_file('gs://storage-bucket/file-name');
 * ```
 *
 * @param string $cloud_storage_uri Your Cloud Storage bucket URI
 * @param string $projectId (optional) Your Google Cloud Project ID
 */

function classify_text_from_file($cloud_storage_uri, $projectId = null)
{
    // Create the Natural Language client
    $language = new LanguageClient([
        'projectId' => $projectId,
    ]);

    // Call the classifyText function
    $response = $language->classifyText($cloud_storage_uri);
    $categories = $response->categories();

    // Print out information about each category
    foreach ($categories as $category) {
        printf('Category Name: %s' . PHP_EOL, $category['name']);
        printf('Confidence: %s' . PHP_EOL, $category['confidence']);
        printf(PHP_EOL);
    }
}
# [END language_classify_file]
