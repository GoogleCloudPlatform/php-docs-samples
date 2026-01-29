<?php
/**
 * Copyright 2026 Google LLC.
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

declare(strict_types=1);

namespace Google\Cloud\Samples\SecretManager;

// [START secretmanager_update_regional_secret_rotation]
use Google\Cloud\SecretManager\V1\Secret;
use Google\Cloud\SecretManager\V1\Rotation;
use Google\Cloud\SecretManager\V1\Topic;
use Google\Cloud\SecretManager\V1\Client\SecretManagerServiceClient;
use Google\Cloud\SecretManager\V1\UpdateSecretRequest;
use Google\Protobuf\Timestamp;
use Google\Protobuf\Duration;
use Google\Protobuf\FieldMask;

/**
 * Update rotation for a regional secret.
 *
 * @param string $projectId Your Google Cloud Project ID (e.g. 'my-project')
 * @param string $locationId Secret location (e.g. 'us-central1')
 * @param string $secretId  Your secret ID (e.g. 'my-secret')
 * @param string $topicName The Pub/Sub topic name for rotation notifications (e.g. 'projects/my-project/topics/my-topic')
 */
function update_regional_secret_rotation(string $projectId, string $locationId, string $secretId, string $topicName): void
{
    $options = ['apiEndpoint' => "secretmanager.$locationId.rep.googleapis.com"];
    $client = new SecretManagerServiceClient($options);

    $name = $client->projectLocationSecretName($projectId, $locationId, $secretId);

    $nextRotationTimeSeconds = time() + 7200; // 2 hours
    $rotationPeriodSeconds = 3600; // 1 hour

    $rotation = new Rotation([
        'next_rotation_time' => new Timestamp(['seconds' => $nextRotationTimeSeconds]),
        'rotation_period' => new Duration(['seconds' => $rotationPeriodSeconds]),
    ]);

    $secret = new Secret([
        'name' => $name,
        'rotation' => $rotation,
        'topics' => [new Topic(['name' => $topicName])],
    ]);

    $fieldMask = new FieldMask();
    $fieldMask->setPaths(['rotation','topics']);

    $request = new UpdateSecretRequest();
    $request->setSecret($secret);
    $request->setUpdateMask($fieldMask);

    $newSecret = $client->updateSecret($request);

    printf('Updated secret: %s%s', $newSecret->getName(), PHP_EOL);
}
// [END secretmanager_update_regional_secret_rotation]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
