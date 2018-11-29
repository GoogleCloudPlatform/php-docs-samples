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

# [START iot_send_command_to_device]
use Google\Cloud\Iot\V1\DeviceManagerClient;

/**
 * Sends a command to a device.
 *
 * @param string $registryId IOT Device Registry ID
 * @param string $deviceId IOT Device ID
 * @param string $command The command sent to a device
 * @param string $projectId Google Cloud project ID
 * @param string $location (Optional) Google Cloud region
 */
function send_command_to_device(
    $registryId,
    $deviceId,
    $command,
    $projectId,
    $location = 'us-central1'
) {
    print('Sending command to device' . PHP_EOL);

    // Instantiate a client.
    $deviceManager = new DeviceManagerClient();
    $deviceName = $deviceManager->deviceName($projectId, $location, $registryId, $deviceId);

    // Response empty on success
    $deviceManager->sendCommandToDevice($deviceName, $command);

    printf('Command sent' . PHP_EOL);
}
# [END iot_send_command_to_device]
