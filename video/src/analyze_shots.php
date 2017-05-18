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

namespace Google\Cloud\Samples\Video;

// [START analyze_shots]
use Google\Cloud\VideoIntelligence\V1beta1\VideoIntelligenceServiceClient;
use google\cloud\videointelligence\v1beta1\Feature;

/**
 * Finds shot changes in the video.
 *
 * @param string $uri The cloud storage object to analyze.  Must be formatted like
 *                    gs://bucketname/objectname
 */
function analyze_shots($uri)
{
    $video = new VideoIntelligenceServiceClient();
    $features = [Feature::SHOT_CHANGE_DETECTION];
    $operationResponse = $video->annotateVideo($uri, $features);
    $operationResponse->pollUntilComplete();
    $textFormat = new  \DrSlump\Protobuf\Codec\TextFormat();
    if ($operationResponse->operationSucceeded()) {
        print $operationResponse->getResult()->serialize($textFormat);
    } else {
        print $operationResponse->getError()->serialize($textFormat);
    }
}
// [END analyze_shots]
