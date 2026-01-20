<?php
/**
 * Copyright 2023 Google LLC.
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

namespace Google\Cloud\Samples\Speech;

use Google\Cloud\Speech\V2\Client\SpeechClient;
use Google\Cloud\Speech\V2\DeleteRecognizerRequest;

/**
 * Delete a recognizer.
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $location The location of the recognizer.
 * @param string $recognizerId The ID of the recognizer to delete.
 */
function delete_recognizer(string $projectId, string $location, string $recognizerId): void
{
    $apiEndpoint = $location === 'global' ? null : sprintf('%s-speech.googleapis.com', $location);
    $speech = new SpeechClient(['apiEndpoint' => $apiEndpoint]);

    // Create the DeleteRecognizerRequest
    $deleteRecognizerRequest = new DeleteRecognizerRequest([
        'name' => SpeechClient::recognizerName($projectId, $location, $recognizerId)
    ]);

    // Call the deleteRecognizer method
    $operation = $speech->deleteRecognizer($deleteRecognizerRequest);

    // Wait for the operation to complete
    $operation->pollUntilComplete();

    if ($operation->operationSucceeded()) {
        printf('Deleted Recognizer: %s' . PHP_EOL, $deleteRecognizerRequest->getName());
    } else {
        print_r($operation->getError());
    }

    $speech->close();
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
