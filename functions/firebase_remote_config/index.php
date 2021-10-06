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

// [START functions_firebase_remote_config]

use Google\CloudFunctions\CloudEvent;

function firebaseRemoteConfig(CloudEvent $cloudevent)
{
    $log = fopen(getenv('LOGGER_OUTPUT') ?: 'php://stderr', 'wb');

    $data = $cloudevent->getData();

    fwrite($log, 'Update type: ' . $data['updateType'] . PHP_EOL);
    fwrite($log, 'Origin: ' . $data['updateOrigin'] . PHP_EOL);
    fwrite($log, 'Version: ' . $data['versionNumber'] . PHP_EOL);
}
// [END functions_firebase_remote_config]
