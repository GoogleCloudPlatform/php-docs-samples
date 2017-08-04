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
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/speech/api/README.md
 */

namespace Google\Cloud\Samples\Speech;

use Exception;
# [START transcribe_async_gcs]
use Google\Cloud\Speech\SpeechClient;
use Google\Cloud\Storage\StorageClient;
use Google\Cloud\Core\ExponentialBackoff;

/**
 * Transcribe an audio file using Google Cloud Speech API
 * Example:
 * ```
 * transcribe_async_gcs('your-bucket-name', 'audiofile.wav');
 * ```.
 *
 * @param string $bucketName The Cloud Storage bucket name.
 * @param string $objectName The Cloud Storage object name.
 * @param string $languageCode The Cloud Storage
 *     be recognized. Accepts BCP-47 (e.g., `"en-US"`, `"es-ES"`).
 * @param array $options configuration options.
 *
 * @return string the text transcription
 */
function transcribe_async_gcs($bucketName, $objectName, $languageCode = 'en-US', $options = [])
{
    // Create the speech client
    $speech = new SpeechClient([
        'languageCode' => $languageCode,
    ]);

    // Fetch the storage object
    $storage = new StorageClient();
    $object = $storage->bucket($bucketName)->object($objectName);

    // Create the asyncronous recognize operation
    $operation = $speech->beginRecognizeOperation(
        $object,
        $options
    );

    // Wait for the operation to complete
    $backoff = new ExponentialBackoff(10);
    $backoff->execute(function () use ($operation) {
        print('Waiting for operation to complete' . PHP_EOL);
        $operation->reload();
        if (!$operation->isComplete()) {
            throw new Exception('Job has not yet completed', 500);
        }
    });

    // Print the results
    if ($operation->isComplete()) {
        $alternatives = $operation->results()[0]->alternatives();
        foreach ($alternatives as $alternative) {
            printf('Transcript: %s' . PHP_EOL, $alternative['transcript']);
            printf('Confidence: %s' . PHP_EOL, $alternative['confidence']);
        }
    }
}
# [END transcribe_async_gcs]
