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

// [START secretmanager_list_tags_on_regional_secret]
use Google\Cloud\ResourceManager\V3\Client\TagBindingsClient;
use Google\Cloud\ResourceManager\V3\ListTagBindingsRequest;

/**
 * List the tag bindings attached to a regional secret.
 *
 * @param string $projectId Your Google Cloud Project ID (e.g. 'my-project')
 * @param string $locationId Your secret location (e.g. 'us-central1')
 * @param string $secretId  Your secret ID (e.g. 'my-secret')
 */
function list_tags_on_regional_secret(string $projectId, string $locationId, string $secretId): void
{
    // Use regional endpoint for Cloud Resource Manager to scope to the location.
    $tagBindOptions = ['apiEndpoint' => "$locationId-cloudresourcemanager.googleapis.com"];
    $tagBindingsClient = new TagBindingsClient($tagBindOptions);

    // Parent must be the full resource name of the secret, including location.
    $parent = sprintf('//secretmanager.googleapis.com/projects/%s/locations/%s/secrets/%s', $projectId, $locationId, $secretId);

    $request = new ListTagBindingsRequest();
    $request->setParent($parent);

    $pagedResponse = $tagBindingsClient->listTagBindings($request);

    foreach ($pagedResponse->iterateAllElements() as $binding) {
        printf('Tag binding: %s with tag value %s%s', $binding->getName(), $binding->getTagValue(), PHP_EOL);
    }
}
// [END secretmanager_list_tags_on_regional_secret]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
