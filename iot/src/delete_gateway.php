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

# [START iot_delete_gateway]
use Google\Cloud\Iot\V1\DeviceManagerClient;

/**
 * Delete the gateway with the given id.
 *
 * @param string $projectId Google Cloud project ID
 * @param string $location (Optional) Google Cloud region
 * @param string $registryId IOT Device Registry ID
 * @param string $gatewayId ID for the gateway to delete
 */
function delete_gateway(
    $projectId,
    $location = 'us-central1',
    $registryId,
    $gatewayId
) {
    print('Deleting Gateway' . PHP_EOL);

    // Instantiate a client.
    $deviceManager = new DeviceManagerClient();
    $gatewayName = $deviceManager->deviceName($projectId, $location, $registryId, $gatewayId);

    // TODO: unbind all bound devices when list_devices_for_gateway
    // is working
    $response = $deviceManager->deleteDevice($gatewayName);

    printf('Deleted %s' . PHP_EOL, $gatewayName);
}
# [END iot_delete_gateway]
