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

// [START secretmanager_delete_tag_from_regional_secret]
use Google\Cloud\ResourceManager\V3\Client\TagBindingsClient;
use Google\Cloud\ResourceManager\V3\ListTagBindingsRequest;
use Google\Cloud\ResourceManager\V3\DeleteTagBindingRequest;

/**
 * Delete a tag binding from a regional secret.
 *
 * @param string $projectId  Your Google Cloud Project ID (e.g. 'my-project')
 * @param string $locationId Your secret location (e.g. 'us-central1')
 * @param string $secretId   Your secret ID (e.g. 'my-secret')
 * @param string $tagValue   Your tag value resource name (e.g. 'tagValues/123')
 */
function delete_tag_from_regional_secret(string $projectId, string $locationId, string $secretId, string $tagValue): void
{
    $tagBindOptions = ['apiEndpoint' => "$locationId-cloudresourcemanager.googleapis.com"];
    $tagBindingsClient = new TagBindingsClient($tagBindOptions);

    $parent = sprintf('//secretmanager.googleapis.com/projects/%s/locations/%s/secrets/%s', $projectId, $locationId, $secretId);

    $request = new ListTagBindingsRequest();
    $request->setParent($parent);

    $pagedResponse = $tagBindingsClient->listTagBindings($request);

    foreach ($pagedResponse->iterateAllElements() as $binding) {
        if ($binding->getTagValue() === $tagValue) {
            $deleteReq = new DeleteTagBindingRequest();
            $deleteReq->setName($binding->getName());

            $operation = $tagBindingsClient->deleteTagBinding($deleteReq);
            $operation->pollUntilComplete();

            if ($operation->operationSucceeded()) {
                printf('Deleted tag binding %s%s', $binding->getName(), PHP_EOL);
            } else {
                $error = $operation->getError();
                printf('Error deleting tag binding: %s%s', $error->getMessage(), PHP_EOL);
            }

            return;
        }
    }

    printf('No tag binding found for tag value %s on secret %s%s', $tagValue, $parent, PHP_EOL);
}
// [END secretmanager_delete_tag_from_regional_secret]

// The following 2 lines are only needed to execute the samples on the CLI
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
