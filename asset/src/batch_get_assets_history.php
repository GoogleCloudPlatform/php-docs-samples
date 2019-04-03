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

# [START asset_quickstart_batch_get_assets_history]
use Google\Cloud\Asset\V1\AssetServiceClient;
use Google\Cloud\Asset\V1\ContentType;
use Google\Cloud\Asset\V1\TimeWindow;
use Google\Protobuf\Timestamp;

function batch_get_assets_history($projectId, $assetNames)
{
    $client = new AssetServiceClient();
    $formattedParent = $client->projectName($projectId);
    $contentType = ContentType::RESOURCE;
    $readTimeWindow = new TimeWindow(['start_time' => new Timestamp(['seconds' => time()])]);

    $resp = $client->batchGetAssetsHistory($formattedParent, $contentType, $readTimeWindow, ['assetNames' => $assetNames]);

    # Do things with response.
    print($resp->serializeToString());
}
# [END asset_quickstart_batch_get_assets_history]
