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

# [START iot_create_registry]
use Google\Cloud\Iot\V1\DeviceManagerClient;
use Google\Cloud\Iot\V1\DeviceRegistry;
use Google\Cloud\Iot\V1\EventNotificationConfig;

/**
 * Creates a registry if it doesn't exist and prints the result.
 *
 * @param string $registryId IOT Device Registry ID
 * @param string $pubsubTopic PubSub topic name for the new registry's event change notification.
 * @param string $projectId Google Cloud project ID
 * @param string $location (Optional) Google Cloud region
 */
function create_registry(
    $registryId,
    $pubsubTopic,
    $projectId,
    $location = 'us-central1'
) {
    print('Creating Registry' . PHP_EOL);

    // The Google Cloud Client Library automatically checks the environment
    // variable GOOGLE_APPLICATION_CREDENTIALS for the Service Account
    // credentials, and defaults scopes to [
    //    'https://www.googleapis.com/auth/cloud-platform',
    //    'https://www.googleapis.com/auth/cloudiot'
    // ].
    $deviceManager = new DeviceManagerClient();
    $locationName = $deviceManager->locationName($projectId, $location);

    $pubsubTopicPath = sprintf('projects/%s/topics/%s', $projectId, $pubsubTopic);
    $eventNotificationConfig = (new EventNotificationConfig)
        ->setPubsubTopicName($pubsubTopicPath);

    $registry = (new DeviceRegistry)
        ->setId($registryId)
        ->setEventNotificationConfigs([$eventNotificationConfig]);

    $registry = $deviceManager->createDeviceRegistry($locationName, $registry);

    printf('Id: %s, Name: %s' . PHP_EOL,
        $registry->getId(),
        $registry->getName());
}
# [END iot_create_registry]
