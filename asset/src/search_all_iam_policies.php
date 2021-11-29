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

// [START asset_quickstart_search_all_iam_policies]
use Google\Cloud\Asset\V1\AssetServiceClient;

/**
 * @param string $scope      Scope of the search
 * @param string $query      (Optional) Query statement
 * @param int    $pageSize   (Optional) Size of each result page
 * @param string $pageToken  (Optional) Token produced by the preceding call
 */
function search_all_iam_policies(
    string $scope,
    string $query = '',
    int $pageSize = 0,
    string $pageToken = ''
) {
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
}
// [END asset_quickstart_search_all_iam_policies]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
