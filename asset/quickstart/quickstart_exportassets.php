<?php
/**
 * Copyright 2016 Google Inc.
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

# [START asset_quickstart_exportassets]
# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

use Google\Cloud\Asset\V1beta1\AssetServiceClient;
use Google\Cloud\Asset\V1beta1\GcsDestination;
use Google\Cloud\Asset\V1beta1\OutputConfig;

// Change this to the path the assets will be exported to, e.g.:
// gs://<bucket-name>/<asset-file-name>. The bucket must exists when running
// this code.
$dumpFilePath = 'YOUR_ASSETS_FILE';
// Change this with your project number.
$project = 'YOUR_PROJECT_ID';
$client = new AssetServiceClient();

$gcsDestination = new GcsDestination(['uri' => $dumpFilePath]);
$outputConfig = new OutputConfig(['gcs_destination' => $gcsDestination]);

$resp = $client->exportAssets("projects/$project", $outputConfig);

$resp->pollUntilComplete();

if ($resp->operationSucceeded()) {
    echo "The result is dumped to $dumpFilePath successfully." . PHP_EOL;
} else {
    $error = $operationResponse->getError();
    // handleError($error)
}
# [END asset_quickstart_exportassets]
