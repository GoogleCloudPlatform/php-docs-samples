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

# [START iot_create_gateway]
use Google\Cloud\Iot\V1\DeviceManagerClient;
use Google\Cloud\Iot\V1\Device;
use Google\Cloud\Iot\V1\DeviceCredential;
use Google\Cloud\Iot\V1\GatewayAuthMethod;
use Google\Cloud\Iot\V1\GatewayConfig;
use Google\Cloud\Iot\V1\GatewayType;
use Google\Cloud\Iot\V1\PublicKeyCredential;
use Google\Cloud\Iot\V1\PublicKeyFormat;

/**
 * Create a new gateway with the given id and certificate file.
 *
 * @param string $projectId (optional) Google Cloud project ID
 * @param string $location (Optional) Google Cloud region
 * @param string $registryId IOT Gateway Registry ID
 * @param string $gatewayId IOT Gateway ID
 * @param string $certificateFile Path to certificate file.
 * @param string $algorithm the algorithm used for JWT (ES256 or RS256).
 */
function create_gateway(
    $projectId,
    $location = 'us-central1',
    $registryId,
    $gatewayId,
    $certificateFile,
    $algorithm
) {
    print('Creating new Gateway' . PHP_EOL);

    // Instantiate a client.
    $deviceManager = new DeviceManagerClient();
    $registryName = $deviceManager->registryName($projectId, $location, $registryId);

    $publicKeyFormat = PublicKeyFormat::ES256_PEM;
    if ($algorithm == 'RS256') {
        $publicKeyFormat = PublicKeyFormat::RSA_X509_PEM;
    }

    $gatewayConfig = (new GatewayConfig())
        ->setGatewayType(GatewayType::GATEWAY)
        ->setGatewayAuthMethod(GatewayAuthMethod::ASSOCIATION_ONLY);

    $publicKey = (new PublicKeyCredential())
        ->setFormat($publicKeyFormat)
        ->setKey(file_get_contents($certificateFile));

    $credential = (new DeviceCredential())
        ->setPublicKey($publicKey);

    $device = (new Device())
        ->setId($gatewayId)
        ->setGatewayConfig($gatewayConfig)
        ->setCredentials([$credential]);

    $gateway = $deviceManager->createDevice($registryName, $device);

    printf('Gateway: %s : %s' . PHP_EOL,
        $gateway->getNumId(),
        $gateway->getId());
}
# [END iot_create_gateway]
