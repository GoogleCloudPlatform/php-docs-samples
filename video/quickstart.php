<?php
/**
 * Copyright 2017 Google Inc.
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

require __DIR__ . '/vendor/autoload.php';

# [START videointelligence_quickstart]
# Imports the Google Cloud client library
use Google\Cloud\VideoIntelligence\V1beta1\VideoIntelligenceServiceClient;
use google\cloud\videointelligence\v1beta1\Feature;

# Instantiate a client.
$video = new VideoIntelligenceServiceClient();

# Execute a request.
$operationResponse = $video->annotateVideo(
    'gs://demomaker/cat.mp4',
    [Feature::LABEL_DETECTION]
);
# Wait for the request to complete.
$operationResponse->pollUntilComplete();
# Print the result.
$textFormat = new \DrSlump\Protobuf\Codec\TextFormat();
if ($operationResponse->operationSucceeded()) {
    print $operationResponse->getResult()->serialize($textFormat);
} else {
    print $operationResponse->getError()->serialize($textFormat);
}
# [END videointelligence_quickstart]
