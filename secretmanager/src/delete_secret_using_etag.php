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

// [START secretmanager_delete_secret_using_etag]
use Google\Cloud\SecretManager\V1\Client\SecretManagerServiceClient;
use Google\Cloud\SecretManager\V1\GetSecretRequest;
use Google\Cloud\SecretManager\V1\DeleteSecretRequest;

/**
 * Delete a secret using a stored etag (optimistic concurrency).
 *
 * @param string $projectId Your Google Cloud Project ID (e.g. 'my-project')
 * @param string $secretId  Your secret ID (e.g. 'my-secret')
 */
function delete_secret_using_etag(string $projectId, string $secretId): void
{
    $client = new SecretManagerServiceClient();

    $name = $client->secretName($projectId, $secretId);

    // Get the current secret to read the etag.
    $getRequest = GetSecretRequest::build($name);
    $current = $client->getSecret($getRequest);

    $etag = $current->getEtag();

    // Build the delete request with the etag.
    $deleteRequest = (new DeleteSecretRequest())
        ->setName($name)
        ->setEtag($etag);

    // Delete the secret.
    $client->deleteSecret($deleteRequest);

    printf('Deleted secret %s' . PHP_EOL, $secretId);
}
// [END secretmanager_delete_secret_using_etag]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);