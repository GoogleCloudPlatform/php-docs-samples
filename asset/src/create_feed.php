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
use Google\Cloud\Asset\V1\FeedOutputConfig;
use Google\Cloud\Asset\V1\PubsubDestination;

/**
 * Create a real time feed.
 *
 * @param string $parent of feeds.
 */
function create_feed($parent, $feedId, $topic, $asset_names)
{
    $client = new AssetServiceClient();
    
    $feed = new Feed();
    $feed_output_config = new FeedOutputConfig();
    $pubsub_destination = new PubsubDestination();
    $pubsub_destination->setTopic($topic);
    $feed_output_config->setPubsubDestination($pubsub_destination);
    $feed->setAssetNames($asset_names);
    $feed->setFeedOutputConfig($feed_output_config);

    $created_feed = $client->CreateFeed($parent, $feedId, $feed);

    echo $created_feed->getName();
}
# [END asset_quickstart_create_feed]
