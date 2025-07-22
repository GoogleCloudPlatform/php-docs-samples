<?php
/*
 * Copyright 2025 Google LLC.
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

// [START secretmanager_update_secret_with_delayed_destroy]
// Import the Secret Manager client library.
use Google\Cloud\SecretManager\V1\Secret;
use Google\Cloud\SecretManager\V1\Client\SecretManagerServiceClient;
use Google\Cloud\SecretManager\V1\UpdateSecretRequest;
use Google\Protobuf\Duration;
use Google\Protobuf\FieldMask;

/**
 * @param string $projectId      Your Google Cloud Project ID (e.g. 'my-project')
 * @param string $secretId       Your secret ID (e.g. 'my-secret')
 * @param int $versionDestroyTtl Your Version Destroy Ttl (e.g. 86400)
 */
function update_secret_with_delayed_destroy(string $projectId, string $secretId, int $versionDestroyTtl): void
{
    // Create the Secret Manager client.
    $client = new SecretManagerServiceClient();

    // Build the resource name of the secret.
    $name = $client->secretName($projectId, $secretId);

    // Build the secret.
    $secret = new Secret([
        'name' => $name,
        'version_destroy_ttl' => new Duration([
            'seconds' => $versionDestroyTtl,
        ])
    ]);

    // Set the field mask.
    $fieldMask = new FieldMask();
    $fieldMask->setPaths(['version_destroy_ttl']);

    // Build the request.
    $request = new UpdateSecretRequest();
    $request->setSecret($secret);
    $request->setUpdateMask($fieldMask);

    // Update the secret.
    $newSecret = $client->updateSecret($request);

    // Print the new secret name.
    printf('Updated secret: %s', $newSecret->getName());
}
// [END secretmanager_update_secret_with_delayed_destroy]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
