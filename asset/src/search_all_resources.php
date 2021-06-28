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

namespace Google\Cloud\Samples\Asset;

// [START asset_quickstart_search_all_resources]
use Google\Cloud\Asset\V1\AssetServiceClient;

/**
 * @param string       $scope      Scope of the search
 * @param string       $query      (Optional) Query statement
 * @param string|array $assetTypes (Optional) Asset types to search for
 * @param int          $pageSize   (Optional) Size of each result page
 * @param string       $pageToken  (Optional) Token produced by the preceding call
 * @param string       $orderBy    (Optional) Fields to sort the results
 */
function search_all_resources(
    string $scope,
    string $query = '',
    array $assetTypes = [],
    int $pageSize = 0,
    string $pageToken = '',
    string $orderBy = ''
) {
    // Instantiate a client.
    $asset = new AssetServiceClient();

    // Run request
    $response = $asset->searchAllResources($scope, [
        'query' => $query,
        'assetTypes' => $assetTypes,
        'pageSize' => $pageSize,
        'pageToken' => $pageToken,
        'orderBy' => $orderBy
    ]);

    // Print the resource names in the first page of the result
    foreach ($response->getPage() as $resource) {
        print($resource->getName() . PHP_EOL);
    }
}
// [END asset_quickstart_search_all_resources]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
