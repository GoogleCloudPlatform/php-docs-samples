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

// [START secretmanager_update_regional_secret_using_etag]
use Google\Cloud\SecretManager\V1\Secret;
use Google\Cloud\SecretManager\V1\GetSecretRequest;
use Google\Cloud\SecretManager\V1\UpdateSecretRequest;
use Google\Cloud\SecretManager\V1\Client\SecretManagerServiceClient;
use Google\Protobuf\FieldMask;

/**
 * Update a regional secret using an etag for optimistic concurrency.
 *
 * This sample fetches the current secret, reads its etag, then attempts to
 * update the secret's labels while providing the etag. The update will
 * succeed only if the etag matches the server's current etag.
 *
 * @param string $projectId Your Google Cloud Project ID (e.g. 'my-project')
 * @param string $locationId Secret location (e.g. 'us-central1')
 * @param string $secretId  Your secret ID (e.g. 'my-secret')
 * @param string $labelKey  Label key to set
 * @param string $labelValue Label value to set
 */
function update_regional_secret_using_etag(string $projectId, string $locationId, string $secretId, string $labelKey, string $labelValue): void
{
    $options = ['apiEndpoint' => "secretmanager.$locationId.rep.googleapis.com"];
    $client = new SecretManagerServiceClient($options);

    $name = $client->projectLocationSecretName($projectId, $locationId, $secretId);

    $getRequest = GetSecretRequest::build($name);
    $current = $client->getSecret($getRequest);

    $etag = $current->getEtag();

    // Prepare the secret with the updated labels and the stored etag.
    $secret = (new Secret())
        ->setName($name)
        ->setLabels([$labelKey => $labelValue])
        ->setEtag($etag);

    // Only update the labels field.
    $updateMask = (new FieldMask())->setPaths(['labels']);

    // Build and send the update request.
    $request = UpdateSecretRequest::build($secret, $updateMask);

    $response = $client->updateSecret($request);

    printf('Updated secret using etag: %s' . PHP_EOL, $response->getName());
}
// [END secretmanager_update_regional_secret_using_etag]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);