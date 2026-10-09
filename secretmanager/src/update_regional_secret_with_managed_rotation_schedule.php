<?php
/*
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

/*
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/secretmanager/README.md
 */

declare(strict_types=1);

namespace Google\Cloud\Samples\SecretManager;

// [START secretmanager_update_regional_secret_with_managed_rotation_schedule]
use Google\Cloud\SecretManager\V1\Secret;
use Google\Cloud\SecretManager\V1\Rotation;
use Google\Cloud\SecretManager\V1\Client\SecretManagerServiceClient;
use Google\Cloud\SecretManager\V1\UpdateSecretRequest;
use Google\Protobuf\Timestamp;
use Google\Protobuf\Duration;
use Google\Protobuf\FieldMask;

/**
 * Updates the rotation schedule of a CLOUD_SQL_DB_CREDENTIALS typed secret.
 *
 * @param string $projectId Your Google Cloud Project ID (e.g. 'my-project')
 * @param string $locationId Your secret Location (e.g. 'us-central1')
 * @param string $secretId  Your secret ID (e.g. 'my-secret')
 * @param int $rotationPeriodSeconds Rotation period in seconds (e.g. 3600)
 */
function update_regional_secret_with_managed_rotation_schedule(string $projectId, string $locationId, string $secretId, int $rotationPeriodSeconds): void
{
    // Specify regional endpoint.
    $options = ['apiEndpoint' => "secretmanager.$locationId.rep.googleapis.com"];

    // Create the Secret Manager client.
    $client = new SecretManagerServiceClient($options);

    // Build the resource name of the secret.
    $name = $client->projectLocationSecretName($projectId, $locationId, $secretId);

    // The rotation schedule of a CLOUD_SQL_DB_CREDENTIALS secret can be set
    // before or after enabling managed rotation; EnableManagedRotation does not
    // need to be called first. Other secret types also support a rotation
    // schedule, but only when Pub/Sub topics are configured. Pub/Sub topics are
    // not required for CLOUD_SQL_DB_CREDENTIALS.
    // next_rotation_time and rotation_period must be set together.
    $nextRotationTimeSeconds = time() + $rotationPeriodSeconds;

    $rotation = new Rotation([
        'next_rotation_time' => new Timestamp(['seconds' => $nextRotationTimeSeconds]),
        'rotation_period' => new Duration(['seconds' => $rotationPeriodSeconds]),
    ]);

    $secret = new Secret([
        'name' => $name,
        'rotation' => $rotation,
    ]);

    // Mask only the rotation subfields being set, not the whole "rotation"
    // submessage.
    $fieldMask = new FieldMask();
    $fieldMask->setPaths(['rotation.next_rotation_time', 'rotation.rotation_period']);

    $request = (new UpdateSecretRequest())
        ->setSecret($secret)
        ->setUpdateMask($fieldMask);

    // Update the secret.
    $updatedSecret = $client->updateSecret($request);

    printf('Updated regional secret rotation schedule: %s%s', $updatedSecret->getName(), PHP_EOL);
}
// [END secretmanager_update_regional_secret_with_managed_rotation_schedule]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
