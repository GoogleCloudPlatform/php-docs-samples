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

// [START secretmanager_create_regional_secret_with_cmek]
use Google\Cloud\SecretManager\V1\CreateSecretRequest;
use Google\Cloud\SecretManager\V1\CustomerManagedEncryption;
use Google\Cloud\SecretManager\V1\Secret;
use Google\Cloud\SecretManager\V1\Client\SecretManagerServiceClient;

/**
 * Create a regional secret that uses a customer-managed encryption key (CMEK).
 *
 * @param string $projectId Google Cloud project id (e.g. 'my-project-id')
 * @param string $locationId Secret location (e.g. 'us-central1')
 * @param string $secretId Id for the new secret (e.g. 'my-secret-id')
 * @param string $kmsKeyName Full KMS key resource name (e.g. 'projects/my-project/locations/global/keyRings/my-kr/cryptoKeys/my-key')
 */
function create_regional_secret_with_cmek(string $projectId, string $locationId, string $secretId, string $kmsKeyName): void
{
    $options = ['apiEndpoint' => "secretmanager.$locationId.rep.googleapis.com"];
    $client = new SecretManagerServiceClient($options);

    $parent = $client->locationName($projectId, $locationId);

    $cmek = new CustomerManagedEncryption([
        'kms_key_name' => $kmsKeyName,
    ]);

    $secret = new Secret([
        'customer_managed_encryption' => $cmek
    ]);

    $request = CreateSecretRequest::build($parent, $secretId, $secret);

    $created = $client->createSecret($request);

    printf('Created secret %s with CMEK %s%s', $created->getName(), $kmsKeyName, PHP_EOL);
}
// [END secretmanager_create_regional_secret_with_cmek]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);