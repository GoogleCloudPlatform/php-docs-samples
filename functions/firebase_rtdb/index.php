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

// [START functions_firebase_rtdb]

use Google\CloudFunctions\CloudEvent;

function firebaseRTDB(CloudEvent $cloudevent)
{
    $log = fopen(getenv('LOGGER_OUTPUT') ?: 'php://stderr', 'wb');

    fwrite($log, 'Event: ' . $cloudevent->getId() . PHP_EOL);

    $data = $cloudevent->getData();
    $resource = $data['resource'] ?? '<null>';

    fwrite($log, 'Function triggered by change to: ' . $resource . PHP_EOL);

    $isAdmin = isset($data['auth']['admin']) && $data['auth']['admin'] == true;

    fwrite($log, 'Admin?: ' . var_export($isAdmin, true) . PHP_EOL);
    fwrite($log, 'Delta: ' . json_encode($data['delta'] ?? '') . PHP_EOL);
}
// [END functions_firebase_rtdb]
