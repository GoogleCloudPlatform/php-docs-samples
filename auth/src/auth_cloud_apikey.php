<?php
/**
 * Copyright 2024 Google Inc.
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
/**
 * For instructions on how to run the full sample:
 *
 * @see https://github.com/GoogleCloudPlatform/php-docs-samples/tree/main/auth/README.md
 */

# [START apikeys_authenticate_api_key]
namespace Google\Cloud\Samples\Auth;

use Google\ApiCore\ApiException;
use Google\ApiCore\InsecureCredentialsWrapper;
use Google\ApiCore\PagedListResponse;
use Google\Cloud\Vision\V1\Client\ProductSearchClient;
use Google\Cloud\Vision\V1\ListProductsRequest;
use Google\Cloud\Vision\V1\Product;

/**
 * Authenticate to a cloud API using an API key explicitly.
 * Note: This only works for APIs with support for API keys.
 *
 * @param string $projectId     The Google Cloud project ID.
 * @param string $location      The location name.
 * @param string $apiKey        The API key.
 */
function auth_cloud_apikey(string $projectId, string $location, string $apiKey): void
{
    $formattedParent = ProductSearchClient::locationName($projectId, $location);

    // Create a client.
    $productSearchClient = new ProductSearchClient([
        'apiKey' => $apiKey,
    ]);

    // Prepare the request message.
    $request = (new ListProductsRequest())
        ->setParent($formattedParent);

    // Call the API and handle any network failures.
    try {
        /** @var PagedListResponse $response */
        $response = $productSearchClient->listProducts($request);

        /** @var Product $element */
        foreach ($response as $element) {
            printf('Element data: %s' . PHP_EOL, $element->serializeToJsonString());
        }
    } catch (ApiException $ex) {
        printf('Call failed with message: %s' . PHP_EOL, $ex->getMessage());
    }
}
# [END apikeys_authenticate_api_key]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
