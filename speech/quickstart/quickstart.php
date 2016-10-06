<?php

# [START speech_quickstart]
# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# Imports the Google Cloud client library
use Google\Cloud\Speech\SpeechClient;

# Your Google Cloud Platform project ID
$projectId = 'YOUR_PROJECT_ID';

# Instantiates a client
$speech = new SpeechClient([
    'projectId' => $projectId
]);

# The name of the audio file to transcribe
$fileName = '/resources/audio.raw';

# The audio file's encoding and sample rate
$options = [
    'encoding' => 'LINEAR16',
    'sampleRate' => 16000,
];

# Detects speech in the audio file
$results = $speech->recognize(fopen(__DIR__ . $fileName, 'r'), $options);

echo 'Transcription: '.$results[0]['transcript'];
# [END speech_quickstart]
return $results;
