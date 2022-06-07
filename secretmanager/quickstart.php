<?php
/**
 * Copyright 2020 Google LLC.
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

require_once __DIR__ . '/vendor/autoload.php';

if (count($argv) != 3) {
    return printf("Usage: php %s PROJECT_ID SECRET_ID\n", basename(__FILE__));
}
list($_, $projectId, $secretId) = $argv;

// [START secretmanager_quickstart]
// Import the Secret Manager client library.
use Google\Cloud\SecretManager\V1\Replication;
use Google\Cloud\SecretManager\V1\Replication\Automatic;
use Google\Cloud\SecretManager\V1\Secret;
use Google\Cloud\SecretManager\V1\SecretManagerServiceClient;
use Google\Cloud\SecretManager\V1\SecretPayload;

/** Uncomment and populate these variables in your code */
// $projectId = 'YOUR_GOOGLE_CLOUD_PROJECT' (e.g. 'my-project');
// $secretId = 'YOUR_SECRET_ID' (e.g. 'my-secret');

// Create the Secret Manager client.
$client = new SecretManagerServiceClient();

// Build the parent name from the project.
$parent = $client->projectName($projectId);

// Create the parent secret.
$secret = $client->createSecret($parent, $secretId,
  new Secret([
        'replication' => new Replication([
            'automatic' => new Automatic(),
        ]),
    ])
);

// Add the secret version.
$version = $client->addSecretVersion($secret->getName(), new SecretPayload([
    'data' => 'hello world',
]));

// Access the secret version.
$response = $client->accessSecretVersion($version->getName());

// Print the secret payload.
//
// WARNING: Do not print the secret in a production environment - this
// snippet is showing how to access the secret material.
$payload = $response->getPayload()->getData();
printf('Plaintext: %s' . PHP_EOL, $payload);
// [END secretmanager_quickstart]
