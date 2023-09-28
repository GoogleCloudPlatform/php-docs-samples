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

// [START functions_firebase_analytics]

use Google\CloudFunctions\CloudEvent;

function firebaseAnalytics(CloudEvent $cloudevent): void
{
    $log = fopen(getenv('LOGGER_OUTPUT') ?: 'php://stderr', 'wb');

    $data = $cloudevent->getData();

    fwrite($log, 'Function triggered by the following event:' . $data['resource'] . PHP_EOL);

    $analyticsEvent = $data['eventDim'][0];
    $unixTime = $analyticsEvent['timestampMicros'] / 1000;

    fwrite($log, 'Name: ' . $analyticsEvent['name'] . PHP_EOL);
    fwrite($log, 'Timestamp: ' . gmdate("Y-m-d\TH:i:s\Z", $unixTime) . PHP_EOL);

    $userObj = $data['userDim'];
    fwrite($log, sprintf(
        'Location: %s, %s' . PHP_EOL,
        $userObj['geoInfo']['city'],
        $userObj['geoInfo']['country']
    ));

    fwrite($log, 'Device Model: %s' . $userObj['deviceInfo']['deviceModel'] . PHP_EOL);
}
// [END functions_firebase_analytics]
