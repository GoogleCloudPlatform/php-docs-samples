<?php

/**
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

namespace Google\Cloud\Samples\Translate;

// [START translate_list_language_names]
use Google\Cloud\Translate\TranslateClient;

// $apiKey = 'YOUR-API-KEY'
// $targetLanguage = 'en'; // Print the names of the languages in which language?

$translate = new TranslateClient([
    'key' => $apiKey,
]);
$result = $translate->localizedLanguages([
    'target' => $targetLanguage,
]);
foreach ($result as $lang) {
    print("$lang[code]: $lang[name]\n");
}
// [END translate_list_language_names]
