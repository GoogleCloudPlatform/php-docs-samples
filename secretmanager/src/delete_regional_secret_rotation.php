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

// [START secretmanager_delete_regional_secret_rotation]
use Google\Cloud\SecretManager\V1\Secret;
use Google\Cloud\SecretManager\V1\Client\SecretManagerServiceClient;
use Google\Cloud\SecretManager\V1\UpdateSecretRequest;
use Google\Protobuf\FieldMask;

/**
 * Delete rotation for a regional secret.
 *
 * @param string $projectId Your Google Cloud Project ID (e.g. 'my-project')
 * @param string $locationId Secret location (e.g. 'us-central1')
 * @param string $secretId  Your secret ID (e.g. 'my-secret')
 */
function delete_regional_secret_rotation(string $projectId, string $locationId, string $secretId): void
{
    $options = ['apiEndpoint' => "secretmanager.$locationId.rep.googleapis.com"];
    $client = new SecretManagerServiceClient($options);

    $name = $client->projectLocationSecretName($projectId, $locationId, $secretId);

    $secret = new Secret([
        'name' => $name,
    ]);

    $fieldMask = new FieldMask();
    $fieldMask->setPaths(['rotation', 'topics']);

    $request = new UpdateSecretRequest();
    $request->setSecret($secret);
    $request->setUpdateMask($fieldMask);

    $newSecret = $client->updateSecret($request);

    printf('Updated secret: %s%s', $newSecret->getName(), PHP_EOL);
}
// [END secretmanager_delete_regional_secret_rotation]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
