<?php

/**
 * Copyright 2022 Google LLC.
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
 * For instructions on how to run the samples:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/media/livestream/README.md
 */

namespace Google\Cloud\Samples\Media\LiveStream;

// [START livestream_update_input]
use Google\Cloud\Video\LiveStream\V1\LivestreamServiceClient;
use Google\Cloud\Video\LiveStream\V1\Input;
use Google\Cloud\Video\LiveStream\V1\PreprocessingConfig;
use Google\Protobuf\FieldMask;

/**
 * Updates an input.
 *
 * @param string  $callingProjectId   The project ID to run the API call under
 * @param string  $location           The location of the input
 * @param string  $inputId            The ID of the input to be updated
 */
function update_input(
    string $callingProjectId,
    string $location,
    string $inputId
): void {
    // Instantiate a client.
    $livestreamClient = new LivestreamServiceClient();

    $formattedName = $livestreamClient->inputName($callingProjectId, $location, $inputId);
    $crop = (new PreprocessingConfig\Crop())
        ->setTopPixels(5)
        ->setBottomPixels(5);
    $config = (new PreprocessingConfig())
        ->setCrop($crop);
    $input = (new Input())
        ->setName($formattedName)
        ->setPreprocessingConfig($config);

    $updateMask = new FieldMask([
        'paths' => ['preprocessing_config']
    ]);

    try {
        // Run the input update request. The response is a long-running operation ID.
        $operationResponse = $livestreamClient->updateInput($input, [], $updateMask);
        $operationResponse->pollUntilComplete();
        if ($operationResponse->operationSucceeded()) {
            $result = $operationResponse->getResult();
            // Print results
            printf('Updated input: %s' . PHP_EOL, $result->getName());
        } else {
            $error = $operationResponse->getError();
            // handleError($error)
        }
    } finally {
        $livestreamClient->close();
    }
}
// [END livestream_update_input]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
