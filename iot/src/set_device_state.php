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

# [START iot_set_device_state]
use GuzzleHttp\Client;
use Firebase\JWT\JWT;

/**
 * Set a device's configuration.
 *
 * @param string $registryId IOT Device Registry ID
 * @param string $deviceId IOT Device ID
 * @param string $certificateFile Path to the RSA certificate file
 * @param string $stateData Binary data for the device state
 * @param string $projectId Google Cloud project ID
 * @param string $location (Optional) Google Cloud region
 */
function set_device_state(
    $registryId,
    $deviceId,
    $certificateFile,
    $stateData,
    $projectId,
    $location = 'us-central1'
) {
    print('Set device state' . PHP_EOL);

    // Instantiate an HTTP client.
    $httpClient = new Client();

    // Create/Sign a JWT for device authentication
    // @see https://cloud.google.com/iot/docs/how-tos/credentials/jwts
    $jwt = JWT::encode(
        ['aud' => $projectId, 'iat' => time(), 'exp' => time() + 3600],
        file_get_contents($certificateFile),
        'RS256'
    );

    // Format the device's URL
    $deviceName = sprintf('projects/%s/locations/%s/registries/%s/devices/%s',
        $projectId, $location, $registryId, $deviceId);

    $url = sprintf('https://cloudiotdevice.googleapis.com/v1/%s:setState', $deviceName);

    // Make the HTTP request
    $response = $httpClient->post($url, [
        'json' => [
            'state' => [
                'binaryData' => base64_encode($stateData)
            ]
        ],
        'headers' => [
            'Authorization' => sprintf('Bearer %s', $jwt)
        ]
    ]);

    print('Updated device State' . PHP_EOL);
}
# [END iot_set_device_state]
