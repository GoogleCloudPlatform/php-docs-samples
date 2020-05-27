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

# [START iot_list_gateways]
use Google\Cloud\Iot\V1\DeviceManagerClient;
use Google\Cloud\Iot\V1\GatewayType;
use Google\Protobuf\FieldMask;

/**
 * List gateways in the registry.
 *
 * @param string $projectId Google Cloud project ID
 * @param string $location (Optional) Google Cloud region
 * @param string $registryId IOT Device Registry ID
 */
function list_gateways(
    $projectId,
    $location = 'us-central1',
    $registryId
) {
    print('Listing gateways' . PHP_EOL);

    // Instantiate a client.
    $deviceManager = new DeviceManagerClient();

    // Format the full registry path
    $registryName = $deviceManager->registryName($projectId, $location, $registryId);

    // Pass field mask to retrieve the gateway configuration fields
    $fieldMask = (new FieldMask())->setPaths(['config', 'gateway_config']);

    // Call the API
    $devices = $deviceManager->listDevices($registryName, [
        'fieldMask' => $fieldMask
    ]);

    // Print the result
    $foundGateway = false;
    foreach ($devices->iterateAllElements() as $device) {
        $gatewayConfig = $device->getGatewayConfig();
        $gatewayType = null;
        if ($gatewayConfig != null) {
            $gatewayType = $gatewayConfig->getGatewayType();
        }

        if ($gatewayType == GatewayType::GATEWAY) {
            $foundGateway = true;
            printf('Device: %s : %s' . PHP_EOL,
                $device->getNumId(),
                $device->getId());
        }
    }
    if (!$foundGateway) {
        printf('Registry %s has no gateways' . PHP_EOL, $registryId);
    }
}
# [END iot_list_gateways]
