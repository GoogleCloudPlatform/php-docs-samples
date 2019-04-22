<?php
/**
 * Copyright 2016 Google Inc.
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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/translate/README.md
 */

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) > 2) {
    return printf("Usage: php %s [TARGET_LANGUAGE]\n", __FILE__);
}
$targetLanguage = isset($argv[1]) ? $argv[1] : 'en';

// [START translate_list_language_names]
use Google\Cloud\Translate\TranslateClient;

/** Uncomment and populate these variables in your code */
// $targetLanguage = 'en'; // Language to print the language names in

$translate = new TranslateClient();
$result = $translate->localizedLanguages([
    'target' => $targetLanguage,
]);
foreach ($result as $lang) {
    printf('%s: %s' . PHP_EOL, $lang['code'], $lang['name']);
}
// [END translate_list_language_names]
