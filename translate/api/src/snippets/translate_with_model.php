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


namespace Google\Cloud\Samples\Translate;

// [START translate_text_with_model]
use Google\Cloud\Translate\TranslateClient;

// $text = 'The text to translate.';
// $targetLanguage = 'ja';  // Which language to translate to?
// $model = 'nmt';  // "base" for standard edition, "nmt" for premium

$translate = new TranslateClient();
$result = $translate->translate($text, [
    'target' => $targetLanguage,
    'model'  => $model,
]);
print("Source language: $result[source]\n");
print("Translation: $result[text]\n");
print("Model: $result[model]\n");
// [END translate_text_with_model]
