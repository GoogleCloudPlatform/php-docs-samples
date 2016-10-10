<?php

# [START language_quickstart]
# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Cloud\NaturalLanguage\NaturalLanguageClient;

# Your Google Cloud Platform project ID
$projectId = 'YOUR_PROJECT_ID';

# Instantiates a client
$language = new NaturalLanguageClient([
    'projectId' => $projectId
]);

# The text to analyze
$text = 'Hello, world!';

# Detects the sentiment of the text
$annotation = $language->analyzeSentiment($text);
$sentiment = $annotation->sentiment();

echo 'Text: ' . $text . '
Sentiment: ' . $sentiment['polarity'] . ', ' . $sentiment['magnitude'];
# [END language_quickstart]
return $sentiment;
