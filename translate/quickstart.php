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

# [START translate_quickstart]
# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Cloud\Translate\V3\Client\TranslationServiceClient;
use Google\Cloud\Translate\V3\TranslateTextRequest;

# Your Google Cloud Platform project ID
$projectId = 'YOUR_PROJECT_ID';

# Instantiates a client
$translate = new TranslationServiceClient();

# The text to translate
$text = 'Hello, world!';
# The target language
$targetLanguage = 'ru';

# Translates some text into Russian
$request = (new TranslateTextRequest())
    ->setContents([$text])
    ->setTargetLanguageCode($targetLanguage)
    ->setParent(TranslationServiceClient::locationName($projectId, 'global'));
$response = $translate->translateText($request);

foreach ($response->getTranslations() as $translation) {
    echo 'Text: ' . $text . '
Translation: ' . $translation->getTranslatedText();
}
# [END translate_quickstart]
return $response;
