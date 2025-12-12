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
use Google\Cloud\Speech\V2\ListRecognizersRequest;
use Google\Cloud\Speech\V2\Recognizer;

/**
 * List all recognizers.
 *
 * @param string $projectId The Google Cloud project ID.
 * @param string $location The location of the recognizer.
 */
function list_recognizers(
    string $projectId,
    string $location,
): void {
    $apiEndpoint = $location === 'global' ? null : sprintf('%s-speech.googleapis.com', $location);
    $speech = new SpeechClient(['apiEndpoint' => $apiEndpoint]);

    // Create the ListRecognizersRequest
    $ListRecognizersRequest = new ListRecognizersRequest([
        'parent' => SpeechClient::locationName($projectId, $location),
    ]);

    // Call the ListRecognizers method
    $responses = $speech->listRecognizers($ListRecognizersRequest);

    foreach ($responses as $recognizer) {
        printf('Recognizer name: %s' . PHP_EOL, $recognizer->getName());
    }
}

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
