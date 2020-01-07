<?php
/**
 * Copyright 2020 Google LLC
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

# [START asset_quickstart_create_feed]
use Google\Cloud\Asset\V1\AssetServiceClient;
use Google\Cloud\Asset\V1\Feed;
use Google\Protobuf\FieldMask;

/**
 * Create a real time feed.
 */
function update_feed($feedName, $assetNames)
{
    $client = new AssetServiceClient();
    
    $new_feed = new Feed();
    $new_feed->setName($feedName);
    $new_feed->setAssetNames($assetNames);
    $updateMask = new FieldMask();
    $updateMask->setPaths(['asset_names']);
    
    $updated_feed = $client->UpdateFeed($new_feed, $updateMask);

    echo 'Feed Updated ' . $updated_feed;
}
# [END asset_quickstart_create_feed]
