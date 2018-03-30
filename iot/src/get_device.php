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

# [START iot_get_device]
use Google\Cloud\Iot\V1\DeviceManagerClient;
use Google\Cloud\Iot\V1\PublicKeyFormat;

/**
 * Retrieve the device with the given id.
 *
 * @param string $registryId IOT Device Registry ID
 * @param string $deviceId IOT Device ID
 * @param string $projectId Google Cloud project ID
 * @param string $location (Optional) Google Cloud region
 */
function get_device(
    $registryId,
    $deviceId,
    $projectId,
    $location = 'us-central1'
) {
    print('Getting device' . PHP_EOL);

    // Instantiate a client.
    $deviceManager = new DeviceManagerClient();
    $deviceName = $deviceManager->deviceName($projectId, $location, $registryId, $deviceId);

    $device = $deviceManager->getDevice($deviceName);

    $formats = [
        PublicKeyFormat::UNSPECIFIED_PUBLIC_KEY_FORMAT => 'unspecified',
        PublicKeyFormat::RSA_X509_PEM => 'RSA_X509_PEM',
        PublicKeyFormat::ES256_PEM => 'ES256_PEM',
        PublicKeyFormat::RSA_PEM => 'RSA_PEM',
        PublicKeyFormat::ES256_X509_PEM => 'ES256_X509_PEM',
    ];

    printf('ID: %s' . PHP_EOL, $device->getId());
    printf('Name: %s' . PHP_EOL, $device->getName());
    foreach ($device->getCredentials() as $credential) {
        print('Certificate:' . PHP_EOL);
        printf('    Format: %s' . PHP_EOL,
            $formats[$credential->getPublicKey()->getFormat()]);
        printf('    Expiration: %s' . PHP_EOL,
            $credential->getExpirationTime()->toDateTime()->format('Y-m-d H:i:s'));
    }
    printf('Data: %s' . PHP_EOL, $device->getConfig()->getBinaryData());
    printf('Version: %s' . PHP_EOL, $device->getConfig()->getVersion());
    printf('Update Time: %s' . PHP_EOL,
        $device->getConfig()->getCloudUpdateTime()->toDateTime()->format('Y-m-d H:i:s'));
}
# [END iot_get_device]
