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

if (count($argv) < 2 || count($argv) > 5) {
    return printf("Usage: php %s SCOPE [QUERY] [PAGE_SIZE] [PAGE_TOKEN]\n", __FILE__);
}
list($_, $scope) = $argv;
$query = isset($argv[2]) ? $argv[2] : '';
$pageSize = isset($argv[3]) ? (int) $argv[3] : 0;
$pageToken = isset($argv[4]) ? $argv[4] : '';

// [START asset_quickstart_search_all_iam_policies]
use Google\Cloud\Asset\V1\AssetServiceClient;

/** Uncomment and populate these variables in your code */
// $scope = 'Scope of the search';
// $query = '';      // (Optional) Query statement
// $pageSize = 0;    // (Optional) Size of each result page
// $pageToken = '';  // (Optional) Token produced by the preceding call

// Instantiate a client.
$asset = new AssetServiceClient();

// Run request
$response = $asset->searchAllIamPolicies($scope, [
    'query' => $query,
    'pageSize' => $pageSize,
    'pageToken' => $pageToken
]);

// Print the resources that the policies are set on
foreach ($response->getPage() as $policy) {
    print($policy->getResource() . PHP_EOL);
}
// [END asset_quickstart_search_all_iam_policies]
