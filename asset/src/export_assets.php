<?php
/**
 * Copyright 2018 Google LLC
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

# [START asset_quickstart_export_assets]
use Google\Cloud\Asset\V1\AssetServiceClient;
use Google\Cloud\Asset\V1\GcsDestination;
use Google\Cloud\Asset\V1\OutputConfig;

/**
 * Export assets for given project to specified dump file path.
 *
 * @param string $projectId the project Id for export assets.
 * @param string $dumpFilePath the file path where the assets will be dumped to.
 *        e.g.: gs://[bucket-name]/[asset-file-name].
 */
function export_assets($projectId, $dumpFilePath)
{
    $client = new AssetServiceClient();

    $gcsDestination = new GcsDestination(['uri' => $dumpFilePath]);
    $outputConfig = new OutputConfig(['gcs_destination' => $gcsDestination]);

    $resp = $client->exportAssets("projects/$projectId", $outputConfig);

    $resp->pollUntilComplete();

    if ($resp->operationSucceeded()) {
        print('The result is dumped to $dumpFilePath successfully.' . PHP_EOL);
    } else {
        $error = $resp->getError();
        printf('There was an error: "%s".' . PHP_EOL, $error->getMessage());
        // handleError($error)
    }
}
# [END asset_quickstart_export_assets]
