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

# [START iot_bind_device_to_gateway]
use Google\Cloud\Iot\V1\DeviceManagerClient;

/**
 * Binds a device to a gateway.
 *
 * @param string $registryId IOT Device Registry ID
 * @param string $deviceId the device ID to bind
 * @param string $gatewayId the ID for the gateway to bind to
 * @param string $projectId Google Cloud project ID
 * @param string $location Google Cloud region
 */
function bind_device_to_gateway(
    $registryId,
    $gatewayId,
    $deviceId,
    $projectId,
    $location = 'us-central1'
) {
    print('Binding Device to Gateway' . PHP_EOL);

    // Instantiate a client.
    $deviceManager = new DeviceManagerClient();
    $registryName = $deviceManager->registryName($projectId, $location, $registryId);

    $result = $deviceManager->bindDeviceToGateway($registryName, $gatewayId, $deviceId);

    print('Device bound');
}
# [END iot_bind_device_to_gateway]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
