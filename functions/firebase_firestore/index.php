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

// [START functions_firebase_firestore]

use Google\CloudFunctions\CloudEvent;

function firebaseFirestore(CloudEvent $cloudevent)
{
    $log = fopen(getenv('LOGGER_OUTPUT') ?: 'php://stderr', 'wb');

    fwrite($log, 'Event: ' . $cloudevent->getId() . PHP_EOL);
    fwrite($log, 'Event Type: ' . $cloudevent->getType() . PHP_EOL);

    $data = $cloudevent->getData();

    $resource = $data['resource'];
    fwrite($log, 'Function triggered by event on: ' . $resource . PHP_EOL);

    if (isset($data['oldValue'])) {
        fwrite($log, 'Old value: ' . json_encode($data['oldValue']) . PHP_EOL);
    }

    if (isset($data['value'])) {
        fwrite($log, 'New value: ' . json_encode($data['value']) . PHP_EOL);
    }
}
// [END functions_firebase_firestore]
