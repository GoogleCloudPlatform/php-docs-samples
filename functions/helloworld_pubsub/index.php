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

use Google\CloudFunctions\CloudEvent;

function helloworldPubsub(CloudEvent $event): string
{
    $log = fopen(getenv('LOGGER_OUTPUT') ?: 'php://stderr', 'wb');
    
    $data = $event->getData();
    if (isset($data['data'])) {
        $name = htmlspecialchars(base64_decode($data['data']));
    } else {
        $name = 'World';
    }

    $result = 'Hello, ' . $name . '!';
    fwrite($log, $result . PHP_EOL);
    return $result;
}
// [END functions_helloworld_pubsub]
