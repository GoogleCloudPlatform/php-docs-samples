<?php

/**
 * Copyright 2020 Google LLC.
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

// [START functions_helloworld_storage]

use CloudEvents\V1\CloudEventInterface;
use Google\CloudFunctions\FunctionsFramework;

// Register the function with Functions Framework.
// This enables omitting the `FUNCTIONS_SIGNATURE_TYPE=cloudevent` environment
// variable when deploying. The `FUNCTION_TARGET` environment variable should
// match the first parameter.
FunctionsFramework::cloudEvent('helloGCS', 'helloGCS');

function helloGCS(CloudEventInterface $cloudevent)
{
    // This function supports all Cloud Storage event types.
    $log = fopen(getenv('LOGGER_OUTPUT') ?: 'php://stderr', 'wb');
    $data = $cloudevent->getData();
    fwrite($log, 'Event: ' . $cloudevent->getId() . PHP_EOL);
    fwrite($log, 'Event Type: ' . $cloudevent->getType() . PHP_EOL);
    fwrite($log, 'Bucket: ' . $data['bucket'] . PHP_EOL);
    fwrite($log, 'File: ' . $data['name'] . PHP_EOL);
    fwrite($log, 'Metageneration: ' . $data['metageneration'] . PHP_EOL);
    fwrite($log, 'Created: ' . $data['timeCreated'] . PHP_EOL);
    fwrite($log, 'Updated: ' . $data['updated'] . PHP_EOL);
}

// [END functions_helloworld_storage]
