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

// [START secretmanager_edit_secret_labels]
// Import the Secret Manager client library.
use Google\Cloud\SecretManager\V1\Client\SecretManagerServiceClient;
use Google\Cloud\SecretManager\V1\GetSecretRequest;
use Google\Cloud\SecretManager\V1\UpdateSecretRequest;
use Google\Protobuf\FieldMask;

/**
 * @param string $projectId  Your Google Cloud Project ID (e.g. 'my-project')
 * @param string $secretId   Your secret ID (e.g. 'my-secret')
 * @param string $labelKey   Your label key (e.g. 'label-key')
 * @param string $labelValue Your label value (e.g. 'label-value')
 */
function edit_secret_labels(string $projectId, string $secretId, string $labelKey, string $labelValue): void
{
    // Create the Secret Manager client.
    $client = new SecretManagerServiceClient();

    // Build the resource name of the parent project.
    $name = $client->secretName($projectId, $secretId);

    // Build the request.
    $request = GetSecretRequest::build($name);

    // get the secret.
    $getSecret = $client->getSecret($request);

    // get the secret labels
    $labels = $getSecret->getLabels();

    // update the label - need to create a new labels map with the updated values
    $newLabels = [];
    foreach ($labels as $key => $value) {
        $newLabels[$key] = $value;
    }
    $newLabels[$labelKey] = $labelValue;
    $getSecret->setLabels($newLabels);

    // set the field mask
    $fieldMask = new FieldMask();
    $fieldMask->setPaths(['labels']);

    // build the secret
    $request = new UpdateSecretRequest();
    $request->setSecret($getSecret);
    $request->setUpdateMask($fieldMask);

    // update the secret
    $updateSecret = $client->updateSecret($request);

    // print the updated secret
    printf('Updated secret %s labels' . PHP_EOL, $updateSecret->getName());
}
// [END secretmanager_edit_secret_labels]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
