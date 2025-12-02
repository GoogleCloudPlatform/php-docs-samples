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

# [START language_quickstart]
# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Cloud\Language\V2\AnalyzeSentimentRequest;
use Google\Cloud\Language\V2\Client\LanguageServiceClient;
use Google\Cloud\Language\V2\Document;

# Your Google Cloud Platform project ID
$projectId = 'YOUR_PROJECT_ID';

# Instantiates a client
$language = new LanguageServiceClient([
    'projectId' => $projectId
]);

# The text to analyze
$text = 'Hello, world!';
$document = (new Document())
    ->setContent($text)
    ->setType(Document\Type::PLAIN_TEXT);
$analyzeSentimentRequest = (new AnalyzeSentimentRequest())
    ->setDocument($document);

# Detects the sentiment of the text
$response = $language->analyzeSentiment($analyzeSentimentRequest);
foreach ($response->getSentences() as $sentence) {
    $sentiment = $sentence->getSentiment();
    echo 'Text: ' . $sentence->getText()->getContent() . PHP_EOL;
    printf('Sentiment: %s, %s' . PHP_EOL, $sentiment->getScore(), $sentiment->getMagnitude());
}

# [END language_quickstart]
return $sentiment ?? null;
