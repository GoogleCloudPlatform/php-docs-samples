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

// Include Google Cloud dependendencies using Composer
require_once __DIR__ . '/../vendor/autoload.php';

if (count($argv) < 2 || count($argv) > 7) {
    return printf("Usage: php %s SCOPE [QUERY] [ASSET_TYPES] [PAGE_SIZE] [PAGE_TOKEN] [ORDER_BY]\n", __FILE__);
}
list($_, $scope) = $argv;
$query = isset($argv[2]) ? $argv[2] : '';
$assetTypes = isset($argv[3]) ? $argv[3] : '';
$pageSize = isset($argv[4]) ? (int) $argv[4] : 0;
$pageToken = isset($argv[5]) ? $argv[5] : '';
$orderBy = isset($argv[6]) ? $argv[6] : '';

// [START asset_quickstart_search_all_resources]
use Google\Cloud\Asset\V1\AssetServiceClient;

/** Uncomment and populate these variables in your code */
// $scope = 'Scope of the search';
// $query = '';      // (Optional) Query statement
// $assetTypes = ''; // (Optional) Asset types to search for
// $pageSize = 0;    // (Optional) Size of each result page
// $pageToken = '';  // (Optional) Token produced by the preceding call
// $orderBy = '';    // (Optional) Fields to sort the results

// Instantiate a client.
$asset = new AssetServiceClient();

// Run request
$response = $asset->searchAllResources($scope, [
    'query' => $query,
    'assetTypes' => empty($assetTypes) ? [] : explode(',', $assetTypes),
    'pageSize' => $pageSize,
    'pageToken' => $pageToken,
    'orderBy' => $orderBy
]);

// Print the resource names in the first page of the result
foreach ($response->getPage() as $resource) {
    print($resource->getName() . PHP_EOL);
}
// [END asset_quickstart_search_all_resources]
