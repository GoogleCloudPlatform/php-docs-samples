<?php
/*
 * Copyright 2016 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
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

namespace Google\Cloud\Samples\Language;

# [START analyze_everything]
use Google\Cloud\NaturalLanguage\NaturalLanguageClient;
use Google\Cloud\NaturalLanguage\Annotation;

/**
 * Find the everything in text.
 * ```
 * analyze_everything('Do you know the way to San Jose?');
 * ```.
 *
 * @param string $text The text to analyze.
 *
 * @return Annotation
 */
function analyze_everything($text, $options = [])
{
    $language = new NaturalLanguageClient();
    $annotation = $language->annotateText($text, [
        'features' => ['entities', 'syntax', 'sentiment']
    ]);
    return $annotation;
}
# [END analyze_everything]
