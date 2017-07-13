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

// [START analyze_safe_search]
use Google\Cloud\VideoIntelligence\V1beta1\VideoIntelligenceServiceClient;
use Google\Cloud\Videointelligence\V1beta1\Feature;

/**
 * Analyze safe search in the video.
 *
 * @param string $uri The cloud storage object to analyze. Must be formatted
 *                    like gs://bucketname/objectname
 */
function analyze_safe_search($uri)
{
    # Instantiate a client.
    $video = new VideoIntelligenceServiceClient();

    # Execute a request.
    $operation = $video->annotateVideo(
        $uri,
        [Feature::SAFE_SEARCH_DETECTION]);

    # Wait for the request to complete.
    $operation->pollUntilComplete();

    # Print the result.
    if ($operation->operationSucceeded()) {
        $likelihoods = ['Unknown', 'Very unlikely', 'Unlikely', 'Possible',
                        'Likely', 'Very likely'];
        $results = $operation->getResult()->getAnnotationResults()[0];
        foreach ($results->getSafeSearchAnnotations() as $safeSearch) {
            printf('At %ss:' . PHP_EOL, $safeSearch->getTimeOffset() / 1000000);
            print('  adult: ' . $likelihoods[$safeSearch->getAdult()] . PHP_EOL);
            print('  spoof: ' . $likelihoods[$safeSearch->getSpoof()] . PHP_EOL);
            print('  medical: ' . $likelihoods[$safeSearch->getMedical()] . PHP_EOL);
            print('  racy: ' . $likelihoods[$safeSearch->getRacy()] . PHP_EOL);
            print('  violent: ' . $likelihoods[$safeSearch->getViolent()] . PHP_EOL);
        }
    } else {
        print_r($operation->getError());
    }
}
// [END analyze_safe_search]
