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

# [START iot_set_iam_policy]
use Google\Cloud\Iot\V1\DeviceManagerClient;
use Google\Cloud\Iam\V1\Policy;
use Google\Cloud\Iam\V1\Binding;

/**
 * Sets IAM permissions for the given registry to a single role/member.
 *
 * @param string $registryId IOT Device Registry ID
 * @param string $role Role used for IAM commands
 * @param string $member Member used for IAM commands
 * @param string $projectId Google Cloud project ID
 * @param string $location (Optional) Google Cloud region
 */
function set_iam_policy(
    $registryId,
    $role,
    $member,
    $projectId,
    $location = 'us-central1'
) {
    print('Setting IAM policy' . PHP_EOL);

    // Instantiate a client.
    $deviceManager = new DeviceManagerClient();
    $registryName = $deviceManager->registryName($projectId, $location, $registryId);

    $binding = (new Binding())
        ->setMembers([$member])
        ->setRole($role);

    $policy = (new Policy())
        ->setBindings([$binding]);

    $policy = $deviceManager->setIamPolicy($registryName, $policy);

    print($policy->serializeToJsonString() . PHP_EOL);
}
# [END iot_set_iam_policy]
