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

if (count($argv) != 2) {
    return printf("Usage: php %s PROJECT_ID\n", basename(__FILE__));
}
list($_, $projectId) = $argv;

// [START secretmanager_list_secrets]
// Import the Secret Manager client library.
use Google\Cloud\SecretManager\V1\SecretManagerServiceClient;

/** Uncomment and populate these variables in your code */
// $projectId = 'YOUR_GOOGLE_CLOUD_PROJECT' (e.g. 'my-project');

// Create the Secret Manager client.
$client = new SecretManagerServiceClient();

// Build the resource name of the parent secret.
$parent = $client->projectName($projectId);

// List all secrets.
foreach ($client->listSecrets($parent) as $secret) {
    printf('Found secret %s', $secret->getName());
}
// [END secretmanager_list_secrets]
