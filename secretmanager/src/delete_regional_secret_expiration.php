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

// [START secretmanager_delete_regional_secret_expiration]
use Google\Cloud\SecretManager\V1\Secret;
use Google\Cloud\SecretManager\V1\UpdateSecretRequest;
use Google\Cloud\SecretManager\V1\Client\SecretManagerServiceClient;
use Google\Protobuf\FieldMask;

/**
 * Delete the expiration TTL from a regional secret.
 *
 * @param string $projectId Google Cloud project id (e.g. 'my-project')
 * @param string $locationId Secret location (e.g. 'us-central1')
 * @param string $secretId  Secret id (e.g. 'my-secret')
 */
function delete_regional_secret_expiration(string $projectId, string $locationId, string $secretId): void
{
    // Create the Secret Manager client.
    $options = ['apiEndpoint' => "secretmanager.$locationId.rep.googleapis.com"];
    $client = new SecretManagerServiceClient($options);

    // Build the resource name of the secret.
    $name = $client->projectLocationSecretName($projectId, $locationId, $secretId);

    // Build the secret with only the name — leaving ttl unset clears it when used with an update mask.
    $secret = new Secret([
        'name' => $name,
    ]);

    // Set the field mask to clear the ttl field.
    $fieldMask = new FieldMask();
    $fieldMask->setPaths(['ttl']);

    // Build the request.
    $request = new UpdateSecretRequest();
    $request->setSecret($secret);
    $request->setUpdateMask($fieldMask);

    // Update the secret.
    $newSecret = $client->updateSecret($request);

    // Print the new secret name.
    printf('Updated secret: %s%s', $newSecret->getName(), PHP_EOL);
}
// [END secretmanager_delete_regional_secret_expiration]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);