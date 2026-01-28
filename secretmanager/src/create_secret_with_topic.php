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

// [START secretmanager_create_secret_with_topic]
use Google\Cloud\SecretManager\V1\CreateSecretRequest;
use Google\Cloud\SecretManager\V1\Replication;
use Google\Cloud\SecretManager\V1\Replication\Automatic;
use Google\Cloud\SecretManager\V1\Secret;
use Google\Cloud\SecretManager\V1\Topic;
use Google\Cloud\SecretManager\V1\Client\SecretManagerServiceClient;

/**
 * Create a secret and associate it with a Pub/Sub topic.
 *
 * @param string $projectId Google Cloud project id (e.g. 'my-project')
 * @param string $secretId Id for the new secret (e.g. 'my-secret')
 * @param string $topicName Full topic resource name (projects/{project}/topics/{topic})
 */
function create_secret_with_topic(string $projectId, string $secretId, string $topicName): void
{
    $client = new SecretManagerServiceClient();

    $parent = $client->projectName($projectId);

    $secret = new Secret([
        'replication' => new Replication([
            'automatic' => new Automatic(),
        ]),
        'topics' => [new Topic(['name' => $topicName])],
    ]);

     // Build the request.
    $request = CreateSecretRequest::build($parent, $secretId, $secret);

    // Create the secret.
    $created = $client->createSecret($request);

    printf('Created secret %s with topic %s%s', $created->getName(), $topicName, PHP_EOL);
}
// [END secretmanager_create_secret_with_topic]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);