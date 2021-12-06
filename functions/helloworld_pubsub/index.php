<?php

/**
 * Copyright 2021 Google LLC.
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

// [START functions_helloworld_pubsub]

use CloudEvents\V1\CloudEventInterface;
use Google\CloudFunctions\FunctionsFramework;

// Register the function with Functions Framework.
// This enables omitting the `FUNCTIONS_SIGNATURE_TYPE=cloudevent` environment
// variable when deploying. The `FUNCTION_TARGET` environment variable should
// match the first parameter.
FunctionsFramework::cloudEvent('helloworldPubsub', 'helloworldPubsub');

function helloworldPubsub(CloudEventInterface $event): void
{
    $log = fopen(getenv('LOGGER_OUTPUT') ?: 'php://stderr', 'wb');

    $cloudEventData = $event->getData();
    $pubSubData = base64_decode($cloudEventData['message']['data']);

    $name = $pubSubData ? htmlspecialchars($pubSubData) : 'World';
    fwrite($log, "Hello, $name!" . PHP_EOL);
}
// [END functions_helloworld_pubsub]
