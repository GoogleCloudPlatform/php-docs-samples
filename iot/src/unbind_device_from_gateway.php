<?php

/**
 * Copyright 2019 Google Inc.
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

# [START iot_unbind_device_from_gateway]
use Google\Cloud\Iot\V1\DeviceManagerClient;

/**
 * Unbinds a device from a gateway.
 *
 * @param string $projectId (optional) Google Cloud project ID
 * @param string $location (Optional) Google Cloud region
 * @param string $registryId IOT Device Registry ID
 * @param string $deviceId the device ID to unbind
 * @param string $gatewayId the ID for the gateway to unbind from
 */
function unbind_device_from_gateway(
    $projectId,
    $location = 'us-central1',
    $registryId,
    $gatewayId,
    $deviceId
) {
    print('Unbinding Device from Gateway' . PHP_EOL);

    // Instantiate a client.
    $deviceManager = new DeviceManagerClient();
    $registryName = $deviceManager->registryName($projectId, $location, $registryId);

    $result = $deviceManager->unbindDeviceFromGateway($registryName, $gatewayId, $deviceId);

    print('Device unbound');
}
# [END iot_unbind_device_from_gateway]
