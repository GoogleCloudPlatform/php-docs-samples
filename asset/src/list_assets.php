<?php
/**
 * Copyright 2021 Google LLC.
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

// [START asset_quickstart_list_assets]
use Google\Cloud\Asset\V1\AssetServiceClient;

/**
 * @param string       $projectId  Tthe project Id for list assets.
 * @param string|array $assetTypes (Optional) Asset types to list for.
 * @param int          $pageSize   (Optional) Size of one result page.
 */
function list_assets(string $projectId, array $assetTypes = [], int $pageSize = null)
{
    // Instantiate a client.
    $client = new AssetServiceClient();

    // Run request
    $response = $client->listAssets("projects/$projectId", [
        'assetTypes' => $assetTypes,
        'pageSize' => $pageSize,
    ]);

    // Print the asset names in the result
    foreach ($response->getPage() as $asset) {
        print($asset->getName() . PHP_EOL);
    }
}
// [END asset_quickstart_list_assets]

// The following 2 lines are only needed to run the samples
require_once __DIR__ . '/../../testing/sample_helpers.php';
\Google\Cloud\Samples\execute_sample(__FILE__, __NAMESPACE__, $argv);
