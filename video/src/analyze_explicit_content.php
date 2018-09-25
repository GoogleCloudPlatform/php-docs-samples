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
namespace Google\Cloud\Samples\Video;

// [START video_analyze_explicit_content]
use Google\Cloud\VideoIntelligence\V1\VideoIntelligenceServiceClient;
use Google\Cloud\VideoIntelligence\V1\Feature;

/**
 * Analyze explicit content in the video.
 *
 * @param string $uri The cloud storage object to analyze. Must be formatted
 *                    like gs://bucketname/objectname.
 * @param array $options optional Array of options to pass to
 *                       OperationResponse::pollUntilComplete. This is useful
 *                       for increasing the "pollingIntervalSeconds" option.
 */
function analyze_explicit_content($uri, array $options = [])
{
    # Instantiate a client.
    $video = new VideoIntelligenceServiceClient();

    # Execute a request.
    $operation = $video->annotateVideo([
        'inputUri' => $uri,
        'features' => [Feature::EXPLICIT_CONTENT_DETECTION]
    ]);

    # Wait for the request to complete.
    $operation->pollUntilComplete($options);

    # Print the result.
    if ($operation->operationSucceeded()) {
        $likelihoods = ['Unknown', 'Very unlikely', 'Unlikely', 'Possible',
                        'Likely', 'Very likely'];
        $results = $operation->getResult()->getAnnotationResults()[0];
        $explicitAnnotation = $results->getExplicitAnnotation();
        foreach ($explicitAnnotation->getFrames() as $frame) {
            $timeOffset = $frame->getTimeOffset();
            $seconds = $timeOffset->getSeconds();
            $nanoseconds = floatval($timeOffset->getNanos())/1000000000.00;
            $time = $seconds + $nanoseconds;
            printf('At %ss:' . PHP_EOL, $time);
            printf('  pornography: ' . $likelihoods[$frame->getPornographyLikelihood()] . PHP_EOL);
        }
    } else {
        print_r($operation->getError());
    }
}
// [END video_analyze_explicit_content]
