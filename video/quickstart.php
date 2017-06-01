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

# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

# [START videointelligence_quickstart]
# Import a convenient way to print the response.
use DrSlump\Protobuf\Codec\TextFormat;
# Imports the Google Cloud client library
use Google\Cloud\VideoIntelligence\V1beta1\VideoIntelligenceServiceClient;
use google\cloud\videointelligence\v1beta1\Feature;

# Instantiate a client.
$video = new VideoIntelligenceServiceClient();

# Execute a request.
$operation = $video->annotateVideo(
    'gs://demomaker/cat.mp4',
    [Feature::LABEL_DETECTION]
);
# Wait for the request to complete.
$operation->pollUntilComplete();
# Print the result.
$format = new TextFormat();
if ($operation->operationSucceeded()) {
    print $operation->getResult()->serialize($format);
} else {
    print $operation->getError()->serialize($format);
}
# [END videointelligence_quickstart]
