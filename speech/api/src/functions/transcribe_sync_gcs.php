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

# [START transcribe_sync_gcs]
use Google\Cloud\Speech\SpeechClient;
use Google\Cloud\Storage\StorageClient;

/**
 * Transcribe an audio file using Google Cloud Speech API
 * Example:
 * ```
 * transcribe_sync_gcs('your-bucket-name', 'audiofile.wav');
 * ```.
 *
 * @param string $audioFile path to an audio file.
 * @param string $languageCode The language of the content to
 *     be recognized. Accepts BCP-47 (e.g., `"en-US"`, `"es-ES"`).
 * @param array $options configuration options.
 *
 * @return string the text transcription
 */
function transcribe_sync_gcs($bucketName, $objectName, $languageCode = 'en-US', $options = [])
{
    $speech = new SpeechClient([
        'languageCode' => $languageCode,
    ]);
    $storage = new StorageClient();
    // fetch the storage object
    $object = $storage->bucket($bucketName)->object($objectName);
    $results = $speech->recognize(
        $object,
        $options
    );
    print_r($results);
}
# [END transcribe_sync_gcs]
