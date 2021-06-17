<?php
/*
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

/*
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/master/secretmanager/README.md
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) != 4) {
    return printf("Usage: php %s PROJECT_ID SECRET_ID VERSION_ID\n", basename(__FILE__));
}
list($_, $projectId, $secretId, $versionId) = $argv;

// [START secretmanager_enable_secret_version]
// Import the Secret Manager client library.
use Google\Cloud\SecretManager\V1\SecretManagerServiceClient;

/** Uncomment and populate these variables in your code */
// $projectId = 'YOUR_GOOGLE_CLOUD_PROJECT' (e.g. 'my-project');
// $secretId = 'YOUR_SECRET_ID' (e.g. 'my-secret');
// $versionId = 'YOUR_VERSION_ID' (e.g. 'latest' or '5');

// Create the Secret Manager client.
$client = new SecretManagerServiceClient();

// Build the resource name of the secret version.
$name = $client->secretVersionName($projectId, $secretId, $versionId);

// Enable the secret version.
$response = $client->enableSecretVersion($name);

// Print a success message.
printf('Enabled secret version: %s', $response->getName());
// [END secretmanager_enable_secret_version]
