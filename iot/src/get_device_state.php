<?php

/**
 * Copyright 2018 Google Inc.
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
namespace Google\Cloud\Samples\Iot;

# [START iot_get_device_state]
use Google\Cloud\Iot\V1\DeviceManagerClient;

/**
 * Retrieve a device's state blobs.
 *
 * @param string $registryId IOT Device Registry ID
 * @param string $deviceId IOT Device ID
 * @param string $projectId Google Cloud project ID
 * @param string $location (Optional) Google Cloud region
 */
function get_device_state(
    $registryId,
    $deviceId,
    $projectId,
    $location = 'us-central1'
) {
    print('Getting device state' . PHP_EOL);

    // Instantiate a client.
    $deviceManager = new DeviceManagerClient();
    $deviceName = $deviceManager->deviceName($projectId, $location, $registryId, $deviceId);

    $response = $deviceManager->listDeviceStates($deviceName);

    foreach ($response->getDeviceStates() as $state) {
        print('State:' . PHP_EOL);
        printf('    Data: %s' . PHP_EOL, $state->getBinaryData());
        printf('    Update Time: %s' . PHP_EOL,
            $state->getUpdateTime()->toDateTime()->format('Y-m-d H:i:s'));
    }
}
# [END iot_get_device_state]
