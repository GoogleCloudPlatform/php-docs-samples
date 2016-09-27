<?php

require __DIR__ . '/vendor/autoload.php';

# [START translate_quickstart]
# Imports the Google Cloud client library
use Google\Cloud\Translate\TranslateClient;

# Your Translate API key
$apiKey = 'YOUR_API_KEY';

# Instantiates a client
$translateClient = new TranslateClient([
    'key' => $apiKey
]);

# The text to translate
$text = 'Hello, world!';
# The target language
$target = 'ru';

# Translates some text into Russian
$translation = $translateClient->translate($text, [
    'target' => $target
]);
# [END translate_quickstart]
