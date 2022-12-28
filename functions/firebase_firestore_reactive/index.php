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

// [START functions_firebase_reactive]

use Google\Cloud\Firestore\FirestoreClient;
use Google\CloudFunctions\CloudEvent;

function firebaseReactive(CloudEvent $cloudevent)
{
    $log = fopen(getenv('LOGGER_OUTPUT') ?: 'php://stderr', 'wb');
    $data = $cloudevent->getData();

    $resource = $data['value']['name'];

    $db = new FirestoreClient();

    $docPath = explode('/documents/', $resource)[1];

    $affectedDoc = $db->document($docPath);

    $curValue = $data['value']['fields']['original']['stringValue'];
    $newValue = strtoupper($curValue);

    if ($curValue !== $newValue) {
        fwrite($log, 'Replacing value: ' . $curValue . ' --> ' . $newValue . PHP_EOL);

        $affectedDoc->set(['original' => $newValue]);
    } else {
        // Value is already upper-case
        // Don't perform another write (it might cause an infinite loop)
        fwrite($log, 'Value is already upper-case.' . PHP_EOL);
    }
}
// [END functions_firebase_reactive]
